import { createContext, useContext, useState } from 'react';
import { useCart } from 'react-use-cart';
import { lignesServeurVersItems, itemsVersLignesServeur } from '../utils/panierServeur';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const { items, setItems, emptyCart } = useCart();
    const [token, setToken] = useState(() => {
        try { return localStorage.getItem('token'); } catch { return null; }
    });

    async function connexion(email, motDePasse) {
        const response = await fetch(`${import.meta.env.VITE_API_URL}/api/login_check`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email: email, password: motDePasse }),
        });

        if (!response.ok) {
            throw new Error(
                response.status === 401 ? 'Email ou mot de passe incorrect.' : 'Erreur serveur, réessaie plus tard.'
            );
        }

        const data = await response.json();
        const nouveauToken = data.token;

        try {
            const reponseFusion = await fetch(`${import.meta.env.VITE_API_URL}/api/panier/fusion`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${nouveauToken}`,
                },
                body: JSON.stringify({ lignes: itemsVersLignesServeur(items) }),
            });

            if (reponseFusion.ok) {
                const panier = await reponseFusion.json();
                setItems(lignesServeurVersItems(panier.lignes));
            } else {
                console.error('Fusion du panier refusée :', reponseFusion.status);
            }
        } catch (e) {
            console.error('Fusion du panier impossible :', e);
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