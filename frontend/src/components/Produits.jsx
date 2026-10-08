import React, { useEffect, useState } from 'react';
import { Container, Row, Col, Spinner } from 'react-bootstrap';
import ProduitCard from './ProduitCard';
import { apiFetch } from '../utils/apiFetch';



function moyennePrix(declinaison) {
  const somme = declinaison.reduce((total, declinaison) => {

    const prixNumerique = parseFloat(declinaison?.prix);
    return total + (isNaN(prixNumerique) ? 0 : prixNumerique);
  }, 0);
  return declinaison.length > 0 ? somme / declinaison.length : 0;

}

export const Produits = () => {
  const [produits, setProduits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    apiFetch(`/api/produits`)
      .then((data) => setProduits(data))
      .catch((err) => setErreur(err.message))
      .finally(() => setLoading(false));
  }, []);

  if (erreur) {
    return (
      <Container className="text-center my-5">
        <p className="text-danger">{erreur}</p>
      </Container>
    );
  }

  if (loading) {
    return (
      <Container className="text-center my-5">
        <Spinner animation="border" role="status" variant="danger">
          <span className="visually-hidden">Chargement des produits...</span>
        </Spinner>
      </Container>
    );
  }

  return (
    <Container className="my-5">
      <h1 className="mb-4 text-center">Liste des produits</h1>
      <Row className="g-4">
        {produits.map((produit) => (
          <Col key={produit.id} xs={12} sm={6} md={4} lg={3}>
            <ProduitCard
              id={produit.id}
              nomDuProduit={produit.nomDuProduit}
              description={produit.description}
              urlImage={produit.images?.[0]?.urlImage}
              prix={moyennePrix(produit.declinaisons)}
            />
          </Col>
        ))}
      </Row>
    </Container>
  );
};

export default Produits;