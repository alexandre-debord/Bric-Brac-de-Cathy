
import React, { useEffect, useState } from 'react';
import { useParams } from 'react-router-dom';
import { Container, Spinner, Form, Button, Alert } from 'react-bootstrap';
import { usePanier } from '../hooks/usePanier';
import { versLignePanier } from '../utils/versLignePanier';
import { formaterPrix } from '../utils/formaterPrix';

const ProduitDetail = () => {
  const { id } = useParams();
  const [produit, setProduit] = useState({});
  const [loading, setLoading] = useState(true);
  const [erreur, setErreur] = useState(null);
  const [idChoisi, setIdChoisi] = useState(null);
  const [quantite, setQuantite] = useState(1);
  const { ajouter } = usePanier();
  const [message, setMessage] = useState(null); // { variant, texte } ou null

  useEffect(() => {
    fetch(`${import.meta.env.VITE_API_URL}/api/produits/${id}`)
      .then((response) => {
        if (!response.ok) throw new Error('Produit introuvable');
        return response.json();
      })
      .then((data) => {
        setProduit(data);
        setLoading(false);
      })
      .catch((error) => {
        console.error('Erreur lors de l\'extraction des données :', error.message);
        setErreur(error.message);
        setLoading(false);
      });
  }, [id]);

async function handleAjouter() {
  const declinaison = produit.declinaisons.find((d) => d.id === idChoisi);
  try {
    const ajoute = await ajouter(versLignePanier(produit, declinaison), quantite);
    setMessage(ajoute
      ? { variant: 'success', texte: "Article ajouté au panier avec succès !" }
      : { variant: 'warning', texte: "Quantité indisponible."  });
  } catch (err) {
    setMessage({ variant: 'danger', texte: err.message });
  }
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
  if (erreur) {
    return (
      <Container className="text-center my-5">
        <p className="text-danger">{erreur}</p>
      </Container>
    );
  }


  return (
    <Container className="py-5 text-center">
      <h1>{produit.nomDuProduit}</h1>
      <p>{produit.description}</p>

      <h3 className="mt-4">Choississez votre couleur:</h3>

      <Form.Select
        className="my-3 mx-auto"
        style={{ maxWidth: 300 }}
        value={idChoisi ?? ''}
        onChange={(e) => {
          setIdChoisi(e.target.value === '' ? null : Number(e.target.value));
          setMessage(null);
        }}
      >
        <option value="">Choisir une couleur</option>
        {produit.declinaisons?.map((d) => (
          <option key={d.id} value={d.id} disabled={d.stock <= 0}>
            {d.couleur?.choixCouleur ?? 'Sans couleur'} — {formaterPrix(Number(d.prix))}
            {d.stock <= 0 && ' (épuisé)'}
          </option>
        ))}
      </Form.Select>

      <Form.Control
        type="number"
        min={1}
        className="mx-auto"
        style={{ maxWidth: 120 }}
        value={quantite}
        onChange={(e) => {
          setQuantite(Number(e.target.value));
          setMessage(null);
        }}
      />
      <Button className="mt-3" variant="danger" onClick={handleAjouter} disabled={!idChoisi}>
        Ajouter au panier
      </Button>

      {message && (
        <Alert variant={message.variant} className="mt-3 mx-auto" style={{ maxWidth: 400 }}>
          {message.texte}
        </Alert>
      )}
    </Container>
  );
};
export default ProduitDetail;