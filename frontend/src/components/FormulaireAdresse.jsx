import { useState } from 'react';
import { Form, Button, Alert, Row, Col } from 'react-bootstrap';
import { useApi } from '../hooks/useApi';

export default function FormulaireAdresse({ onCreee, onAnnuler }) {
    const api = useApi();
    const [rue, setRue] = useState('');
    const [codePostal, setCodePostal] = useState('');
    const [ville, setVille] = useState('');
    const [pays, setPays] = useState('France');
    const [erreur, setErreur] = useState(null);
    const [envoi, setEnvoi] = useState(false);

    async function handleSubmit(e) {
        e.preventDefault();
        setErreur(null);
        setEnvoi(true);
        try {
            const nouvelle = await api('/api/adresses', {
                method: 'POST',
                corps: { type: 'livraison', rue, code_postal: codePostal, ville, pays },
            });
            onCreee(nouvelle);
        } catch (err) {
            setErreur(err.message);
        } finally {
            setEnvoi(false);
        }
    }

    return (
        <Form onSubmit={handleSubmit} className="border rounded p-3 mt-3">
            {erreur && <Alert variant="danger">{erreur}</Alert>}

            <Form.Group className="mb-3" controlId="rue">
                <Form.Label>Rue</Form.Label>
                <Form.Control required maxLength={255} autoComplete="street-address"
                    value={rue} onChange={(e) => setRue(e.target.value)} />
            </Form.Group>

            <Row>
                <Col sm={4}>
                    <Form.Group className="mb-3" controlId="codePostal">
                        <Form.Label>Code postal</Form.Label>
                        <Form.Control required inputMode="numeric" pattern="\d{5}" maxLength={5} autoComplete="postal-code"
                            value={codePostal} onChange={(e) => setCodePostal(e.target.value)} />
                    </Form.Group>
                </Col>
                <Col sm={8}>
                    <Form.Group className="mb-3" controlId="ville">
                        <Form.Label>Ville</Form.Label>
                        <Form.Control required maxLength={50} autoComplete="address-level2"
                            value={ville} onChange={(e) => setVille(e.target.value)} />
                    </Form.Group>
                </Col>
            </Row>

            <Form.Group className="mb-3" controlId="pays">
                <Form.Label>Pays</Form.Label>
                <Form.Control required maxLength={50} autoComplete="country-name"
                    value={pays} onChange={(e) => setPays(e.target.value)} />
            </Form.Group>

            <div className="d-flex gap-2">
                <Button type="submit" variant="danger" disabled={envoi}>
                    {envoi ? 'Enregistrement…' : 'Enregistrer'}
                </Button>
                <Button type="button" variant="outline-secondary" onClick={onAnnuler}>
                    Annuler
                </Button>
            </div>
        </Form>
    );
}