
import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { Spinner, Form, Button, Alert, Container } from 'react-bootstrap';
import { resumePrix } from '../utils/resumePrix';
import '../css/ProduitDetail.css';
import { usePanier } from '../hooks/usePanier';
import { versLignePanier } from '../utils/versLignePanier';
import { formaterPrix } from '../utils/formaterPrix';
import { apiFetch } from '../utils/apiFetch';
import SuggestionsProduits from './SuggestionsProduits';

const ProduitDetail = () => {
  const { id } = useParams();
  const [produit, setProduit] = useState({});
  const [loading, setLoading] = useState(true);
  const [erreur, setErreur] = useState(null);
  const [idChoisi, setIdChoisi] = useState(null);
  const [quantite, setQuantite] = useState(1);
  const { ajouter } = usePanier();
  const [message, setMessage] = useState(null);
  const [imageActive, setImageActive] = useState(0);

  useEffect(() => {
    setLoading(true);
    setErreur(null);
    setIdChoisi(null);
    setQuantite(1);
    setImageActive(0);
    setMessage(null);
    window.scrollTo(0, 0);

    apiFetch(`/api/produits/${id}`)
      .then((data) => setProduit(data))
      .catch((err) => setErreur(err.status === 404 ? 'Produit introuvable.' : err.message))
      .finally(() => setLoading(false));
  }, [id]);

  async function handleAjouter() {
    try {
      const ajoute = await ajouter(versLignePanier(produit, declinaison), quantite);
      setMessage(ajoute
        ? { variant: 'success', texte: "Article ajouté au panier avec succès !" }
        : { variant: 'warning', texte: "Quantité indisponible." });
    } catch (err) {
      setMessage({ variant: 'danger', texte: err.message });
    }
  }

  if (loading) {
    return (
      <Container className="text-center my-5">
        <Spinner animation="border" role="status">
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



  const declinaison = produit.declinaisons?.find((d) => d.id === idChoisi);
  const infosPrix = resumePrix(produit.declinaisons);
  const images = produit.images ?? [];
  const stockMax = declinaison?.stock ?? 1;

  return (
    <div className="fiche">
      {/* Fil d'Ariane */}
      <nav className="fiche-ariane" aria-label="Fil d'Ariane">
        <ol>
          <li><Link to="/">Accueil</Link></li>
          <li><Link to="/produits">{produit.categorie?.nomCategorie}</Link></li>   {/* nom de la catégorie */}
          <li aria-current="page">{produit.nomDuProduit}</li>          {/* nom du produit */}
        </ol>
      </nav>

      <div className="fiche-grille">
        {/* Galerie */}
        <div className={`fiche-galerie${images.length > 1 ? ' fiche-galerie--vignettes' : ''}`}>
          <div className="fiche-image-principale">
            {images.length > 0 && (
              <img src={images[imageActive].urlImage} alt={produit.nomDuProduit} />
            )}
          </div>

          {images.length > 1 && (
            <ul className="fiche-vignettes">
              {images.map((image, index) => (
                <li key={image.id}>
                  <button
                    type="button"
                    onClick={() => setImageActive(index)}
                    aria-pressed={imageActive === index}
                    aria-label={`Voir l'image ${index + 1}`}
                  >
                    <img src={image.urlImage} alt="" />
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>

        {/* Infos */}
        <div>
          <h1 className="fiche-titre">{produit.nomDuProduit}</h1>
          <p className="fiche-prix">
            {declinaison
              ? formaterPrix(Number(declinaison.prix))
              : infosPrix && <>{infosPrix.plusieursPrix && 'dès '}{formaterPrix(infosPrix.min)}</>}
          </p>
          <p className="fiche-description">{produit.description}</p>

          <Form.Group className="fiche-champ" controlId="choix-couleur">
            <Form.Label className="libelle">Couleur</Form.Label>
            <Form.Select
              value={idChoisi ?? ''}
              onChange={(e) => {
                setIdChoisi(e.target.value === '' ? null : Number(e.target.value));
                setQuantite(1);   // nouvelle couleur = on repart à 1
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
          </Form.Group>

          <div className="fiche-champ">
            <span className="libelle" id="label-quantite">Quantité</span>
            <div className="fiche-quantite-ligne">
              <div className="fiche-stepper" role="group" aria-labelledby="label-quantite">
                <button type="button" aria-label="Diminuer la quantité"
                  onClick={() => setQuantite((q) => q - 1)} disabled={quantite <= 1} >−</button>
                <span className="fiche-stepper-valeur" aria-live="polite">{quantite}</span>
                <button type="button" aria-label="Augmenter la quantité"
                  onClick={() => setQuantite((q) => q + 1)} disabled={!declinaison || quantite >= stockMax}>+</button>
              </div>
              {declinaison && declinaison.stock > 0 && (
                <p className="fiche-stock">
                  En stock — {declinaison.stock} {declinaison.stock > 1 ? 'pièces restante' : 'pièce restante'}
                </p>
              )}
            </div>
          </div>

          <Button variant="bordeaux" className="fiche-bouton" onClick={handleAjouter} disabled={!idChoisi}>
            Ajouter au panier
          </Button>

          {message && <Alert variant={message.variant} className="mt-3">{message.texte}</Alert>}
        </div>
      </div>
      <SuggestionsProduits idActuel={produit.id} />
    </div>
  );
};

export default ProduitDetail;