import { useState } from 'react';
import { Container, Form, Button, Alert, Row, Col } from 'react-bootstrap';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { apiFetch } from '../utils/apiFetch';

export default function Inscription() {
  const { connexion } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const [prenom, setPrenom] = useState('');
  const [nom, setNom] = useState('');
  const [email, setEmail] = useState('');
  const [motDePasse, setMotDePasse] = useState('');
  const [confirmation, setConfirmation] = useState('');
  const [erreur, setErreur] = useState(null);             // message général
  const [erreursChamps, setErreursChamps] = useState({}); // { email: '...', motDePasse: '...' }
  const [envoi, setEnvoi] = useState(false);

  async function handleSubmit(e) {
    e.preventDefault();
    setErreur(null);
    setErreursChamps({});

    // 1. Vérification locale : elle n'a pas besoin du serveur
    if (motDePasse !== confirmation) {
      setErreursChamps({ confirmation: 'Les deux mots de passe ne correspondent pas.' });
      return;
    }

    setEnvoi(true);
    try {
      // 2. Création du compte (route publique : apiFetch, pas useApi)
      await apiFetch('/api/register', {
        method: 'POST',
        corps: { email, password:motDePasse, nom, prenom },
      });
    } catch (err) {
      const c = err.champs ?? {};
      setErreursChamps({
        prenom: c.prenom?.[0],
        nom: c.nom?.[0],
        email: c.email?.[0],
        motDePasse: c.plainPassword?.[0],
      });
      setErreur(err.message);
      setEnvoi(false);
      return;
    }

    // 3. Compte créé : connexion automatique (et fusion du panier)
    try {
      await connexion(email, motDePasse);
      navigate(location.state?.retour ?? '/', { replace: true });
    } catch {
      // Cas rare : le compte existe, mais la connexion a échoué
      navigate('/connexion', { replace: true, state: location.state });
    } finally {
      setEnvoi(false);
    }
  }

  return (
    <Container className="py-4" style={{ maxWidth: 480 }}>
      <h1 className="h3 mb-3">Créer un compte</h1>
      {erreur && <Alert variant="danger">{erreur}</Alert>}

      <Form onSubmit={handleSubmit}>
        <Row>
          <Col sm={6}>
            <Form.Group className="mb-3" controlId="prenom">
              <Form.Label>Prénom</Form.Label>
              <Form.Control required maxLength={20} autoComplete="given-name"
                value={prenom} onChange={(e) => setPrenom(e.target.value)}
                isInvalid={Boolean(erreursChamps.prenom)} />
              <Form.Control.Feedback type="invalid">{erreursChamps.prenom}</Form.Control.Feedback>
            </Form.Group>
          </Col>
          <Col sm={6}>
            <Form.Group className="mb-3" controlId="nom">
              <Form.Label>Nom</Form.Label>
              <Form.Control required maxLength={20} autoComplete="family-name"
                value={nom} onChange={(e) => setNom(e.target.value)}
                isInvalid={Boolean(erreursChamps.nom)} />
              <Form.Control.Feedback type="invalid">{erreursChamps.nom}</Form.Control.Feedback>
            </Form.Group>
          </Col>
        </Row>

        <Form.Group className="mb-3" controlId="email">
          <Form.Label>Email</Form.Label>
          <Form.Control type="email" required maxLength={255} autoComplete="email"
            value={email} onChange={(e) => setEmail(e.target.value)}
            isInvalid={Boolean(erreursChamps.email)} />
          <Form.Control.Feedback type="invalid">{erreursChamps.email}</Form.Control.Feedback>
        </Form.Group>

        <Form.Group className="mb-3" controlId="motDePasse">
          <Form.Label>Mot de passe</Form.Label>
          <Form.Control type="password" required minLength={8} autoComplete="new-password"
            value={motDePasse} onChange={(e) => setMotDePasse(e.target.value)}
            isInvalid={Boolean(erreursChamps.motDePasse)} />
          <Form.Text muted>8 caractères minimum.</Form.Text>
          <Form.Control.Feedback type="invalid">{erreursChamps.motDePasse}</Form.Control.Feedback>
        </Form.Group>

        <Form.Group className="mb-3" controlId="confirmation">
          <Form.Label>Confirmer le mot de passe</Form.Label>
          <Form.Control type="password" required autoComplete="new-password"
            value={confirmation} onChange={(e) => setConfirmation(e.target.value)}
            isInvalid={Boolean(erreursChamps.confirmation)} />
          <Form.Control.Feedback type="invalid">{erreursChamps.confirmation}</Form.Control.Feedback>
        </Form.Group>

        <Button type="submit" variant="danger" className="w-100" disabled={envoi}>
          {envoi ? 'Création du compte…' : 'Créer mon compte'}
        </Button>
      </Form>

      <p className="text-center mt-3 small">
        Déjà un compte ?{' '}
        <Link to="/connexion" state={location.state}>Se connecter</Link>
      </p>
    </Container>
  );
}