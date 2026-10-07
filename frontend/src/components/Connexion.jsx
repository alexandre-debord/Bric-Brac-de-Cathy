import { useState } from 'react';
import { Container, Form, Button, Alert } from 'react-bootstrap';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';


export default function Connexion() {
    const { connexion } = useAuth();
    const navigate = useNavigate();
    const [email, setEmail] = useState('');
    const [motDePasse, setMotDePasse] = useState('');
    const [erreur, setErreur] = useState(null);
    const [envoi, setEnvoi] = useState(false);
    const location = useLocation();

    async function handleSubmit(e) {
        e.preventDefault();
        setErreur(null);
        setEnvoi(true);
        try {
            await connexion(email, motDePasse);
            navigate(location.state?.retour ?? '/', { replace: true });
        } catch (err) {
            setErreur(err.message === 'Failed to fetch' ? 'Serveur injoignable.' : err.message);
        } finally {
            setEnvoi(false);
        }
    }

    return (
        <Container className="py-4" style={{ maxWidth: 420 }}>
            <h1 className="h3 mb-3">Connexion</h1>
            {erreur && <Alert variant="danger">{erreur}</Alert>}
            <Form onSubmit={handleSubmit}>
                <Form.Group className="mb-3" controlId="email">
                    <Form.Label>Email</Form.Label>
                    <Form.Control type="email" required autoComplete="email"
                        value={email} onChange={(e) => setEmail(e.target.value)} />
                </Form.Group>
                <Form.Group className="mb-3" controlId="motDePasse">
                    <Form.Label>Mot de passe</Form.Label>
                    <Form.Control type="password" required autoComplete="current-password"
                        value={motDePasse} onChange={(e) => setMotDePasse(e.target.value)} />
                </Form.Group>
                <Button type="submit" disabled={envoi} className="w-100">
                    {envoi ? 'Connexion…' : 'Se connecter'}
                </Button>
            </Form>
        </Container>
    );
}