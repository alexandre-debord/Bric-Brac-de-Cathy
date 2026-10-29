import { createContext, useContext, useEffect, useState } from 'react';
import { useCart } from 'react-use-cart';
import { lignesServeurVersItems, itemsVersLignesServeur } from '../utils/panierServeur';
import { apiFetch, ErreurApi } from '../utils/apiFetch';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
    const { items, setItems, emptyCart } = useCart();
    const [utilisateur, setUtilisateur] = useState(null);

    const [token, setToken] = useState(() => {
        try { return localStorage.getItem('token'); } catch { return null; }
    });
    const [chargementUtilisateur, setChargementUtilisateur] = useState(Boolean(token));

    useEffect(() => {
        // 1. Pas de jeton ?
        if (!token) {
            setUtilisateur(null);
            setChargementUtilisateur(false);
            return;
        }

        // 2. Il y a un jeton : on commence le chargement
        setChargementUtilisateur(true);

        apiFetch('/api/me', { token })
            .then((data) => {
                // 3. ranger data dans utilisateur
                setUtilisateur(data);
            })
            .catch((err) => {
                // 4. si err.status vaut 401 → deconnexion()
                if (err.status === 401) {
                    deconnexion();

                } else {
                    console.error("Chargement de l’utilisateur impossible", err.message);
                }
            })
            .finally(() => {
                setChargementUtilisateur(false);
            });
    }, [token]);

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
    const estAdmin = utilisateur?.roles?.includes('ROLE_ADMIN') ?? false;

    return (
        <AuthContext.Provider value={{ token, estConnecte: Boolean(token), connexion, deconnexion, estAdmin, utilisateur, chargementUtilisateur }}>
            {children}
        </AuthContext.Provider>
    );
}

export function useAuth() {
    return useContext(AuthContext);
}