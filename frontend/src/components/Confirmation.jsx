import { useLocation, Link } from 'react-router-dom';
import { Container, Button, Alert } from 'react-bootstrap';
import { formaterPrix } from '../utils/formaterPrix';

const LIBELLES_STATUT = {
    en_attente_paiement: 'En attente de paiement',
    payee: 'Payée',
    annulee: 'Annulée',
    expediee: 'Expédiée',
};

export default function Confirmation() {
    const location = useLocation();
    const commande = location.state?.commande;

    if (!commande) {
        return (
            <Container className="py-5 text-center">
                <p className="text-muted">Aucune commande à afficher.</p>
                <Button as={Link} to="/" variant="bordeaux">Retour à l'accueil</Button>
            </Container>
        );
    }

    return (
        <Container className="py-5 text-center" style={{ maxWidth: 600 }}>
            <h1 className="mb-3">Merci pour ta commande !</h1>
            <Alert variant="success">
                Commande n° <strong>{commande.id_commande}</strong> enregistrée.
            </Alert>
            <p>Total : <strong>{formaterPrix(Number(commande.total_ht))}</strong></p>
            <p className="text-muted">Statut : {LIBELLES_STATUT[commande.statut] ?? commande.statut}</p>
            <Button as={Link} to="/produits" variant="bordeaux">Continuer mes achats</Button>
        </Container>
    );
}