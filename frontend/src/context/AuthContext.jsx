import { createContext, useContext, useState } from 'react';
import { useCart } from 'react-use-cart';
import { lignesServeurVersItems, itemsVersLignesServeur } from '../utils/panierServeur';
import { apiFetch, ErreurApi } from '../utils/apiFetch';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const { items, setItems, emptyCart } = useCart();
    const [token, setToken] = useState(() => {
        try { return localStorage.getItem('token'); } catch { return null; }
    });

    async function connexion(email, motDePasse) {
        let data;
        try {
            data = await apiFetch('/api/login_check', {
                method: 'POST',
                corps: { email, password: motDePasse },
            });
        } catch (err) {
            // Ici, un 401 veut dire « mauvais identifiants », pas « session expirée »
            if (err.status === 401) {
                throw new ErreurApi('Email ou mot de passe incorrect.', 401);
            }
            throw err;
        }

        const nouveauToken = data.token;

        try {
            const panier = await apiFetch('/api/panier/fusion', {
                method: 'POST',
                corps: { lignes: itemsVersLignesServeur(items) },
                token: nouveauToken,
            });
            setItems(lignesServeurVersItems(panier.lignes));
        } catch (err) {
            console.error('Fusion du panier impossible :', err.message);
        }

        try { localStorage.setItem('token', nouveauToken); } catch { }
        setToken(nouveauToken);
    }

    function deconnexion() {
        try { localStorage.removeItem('token'); } catch { }
        setToken(null);
        emptyCart();
    }

    return (
        <AuthContext.Provider value={{ token, estConnecte: Boolean(token), connexion, deconnexion }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    return useContext(AuthContext);
}