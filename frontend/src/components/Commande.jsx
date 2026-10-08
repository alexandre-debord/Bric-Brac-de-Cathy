import { useEffect, useState } from 'react';
import { Navigate, Link, useLocation, useNavigate } from 'react-router-dom';
import { Container, Form, Alert, Spinner, Button } from 'react-bootstrap';
import { useCart } from 'react-use-cart';
import { useAuth } from '../context/AuthContext';
import { usePanier } from '../hooks/usePanier';
import { formaterPrix } from '../utils/formaterPrix';
import FormulaireAdresse from './FormulaireAdresse';
import { useApi } from '../hooks/useApi';



export default function Commande() {
    const { estConnecte } = useAuth();
    const api = useApi();
    const { items, isEmpty, cartTotal } = usePanier();
    const location = useLocation();

    const [adresses, setAdresses] = useState([]);
    const [chargement, setChargement] = useState(true);
    const [erreur, setErreur] = useState(null);
    const [idLivraison, setIdLivraison] = useState(null);
    const [afficherFormulaire, setAfficherFormulaire] = useState(false);
    const navigate = useNavigate();
    const { emptyCart } = useCart();
    const [envoiCommande, setEnvoiCommande] = useState(false);

    useEffect(() => {
        if (!estConnecte) return;

        api('/api/adresses')
            .then((data) => setAdresses(data))
            .catch((err) => setErreur(err.message))
            .finally(() => setChargement(false));
    }, [estConnecte,api]);

    if (!estConnecte) {
        return <Navigate to="/connexion" state={{ retour: location.pathname }} replace />;
    }

    if (isEmpty) {
        return (
            <Container className="py-5 text-center">
                <h1 className="mb-4">Finaliser ma commande</h1>
                <p className="text-muted">Ton panier est vide.</p>
                <Button as={Link} to="/produits" variant="danger">Découvrir la boutique</Button>
            </Container>
        );
    }

    const adressesLivraison = adresses.filter((a) => a.type === 'livraison');

    async function handleCommander() {
        setErreur(null);
        setEnvoiCommande(true);
        try {
            const commande = await api('/api/commandes', {
                method: 'POST',
                corps: { id_adresse_livraison: idLivraison },
            });
            navigate('/commande/confirmation', { replace: true, state: { commande } });
            emptyCart();
        } catch (err) {
            setErreur(err.message);
        } finally {
            setEnvoiCommande(false);
        }
    }

    function adresseCreee(nouvelle) {
        setAdresses((precedentes) => [...precedentes, nouvelle]);
        setIdLivraison(nouvelle.id);
        setAfficherFormulaire(false);
    }


    return (
        <Container className="py-5" style={{ maxWidth: 700 }}>
            <h1 className="mb-4">Finaliser ma commande</h1>
            {erreur && <Alert variant="danger">{erreur}</Alert>}

            <h2 className="h5">Récapitulatif</h2>
            <ul className="list-unstyled mb-2">
                {items.map((item) => (
                    <li key={item.id}>
                        {item.quantity} × {item.nom} {item.couleur && `(${item.couleur})`} — {formaterPrix(item.itemTotal)}
                    </li>
                ))}
            </ul>
            <p className="fw-bold mb-4">Total : {formaterPrix(cartTotal)}</p>

            <h2 className="h5">Adresse de livraison</h2>
            {chargement ? (
                <Spinner animation="border" size="sm" role="status">
                    <span className="visually-hidden">Chargement des adresses…</span>
                </Spinner>
            ) : adressesLivraison.length === 0 ? (
                <p className="text-muted">Aucune adresse de livraison enregistrée.</p>
            ) : (
                <Form>
                    {adressesLivraison.map((a) => (
                        <Form.Check
                            key={a.id}
                            type="radio"
                            id={`livraison-${a.id}`}
                            name="livraison"
                            label={`${a.rue}, ${a.code_postal} ${a.ville} (${a.pays})`}
                            checked={idLivraison === a.id}
                            onChange={() => setIdLivraison(a.id)}
                        />
                    ))}
                </Form>
            )}
            {afficherFormulaire ? (
                <FormulaireAdresse onCreee={adresseCreee} onAnnuler={() => setAfficherFormulaire(false)} />
            ) : (
                <Button variant="link" className="px-0" onClick={() => setAfficherFormulaire(true)}>
                    + Ajouter une adresse
                </Button>
            )}

            <div className="d-grid mt-4">
                <Button variant="danger" size="lg" onClick={handleCommander}
                    disabled={!idLivraison || envoiCommande}>
                    {envoiCommande ? 'Validation…' : 'Valider la commande'}
                </Button>
            </div>
            <p className="small text-muted mt-2 text-center">
                Le paiement en ligne sera disponible prochainement.
            </p>
        </Container>
    );
}