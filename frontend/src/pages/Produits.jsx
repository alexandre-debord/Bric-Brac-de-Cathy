import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { Container, Spinner } from 'react-bootstrap';
import ProduitCard from '../components/ProduitCard';
import { apiFetch } from '../utils/apiFetch';
import { urlImageProduit } from '../utils/urlImageProduit';
import { resumePrix } from '../utils/resumePrix';
import '../css/Catalogue.css';

export const Produits = () => {
  const [produits, setProduits] = useState([]);
  const [loading, setLoading] = useState(true);
  const [erreur, setErreur] = useState(null);
  const [searchParams, setSearchParams] = useSearchParams();

  useEffect(() => {
    apiFetch('/api/produits')
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
        <Spinner animation="border" role="status">
          <span className="visually-hidden">Chargement des produits...</span>
        </Spinner>
      </Container>
    );
  }

  // Catégorie active lue dans l'URL : "?categorie=2" → 2 ; absente ou invalide → null (= Tout)
  const idCategorie = Number(searchParams.get('categorie')) || null;

  // Catégories uniques présentes dans les produits (la Map dédoublonne par id)
  const categories = [
    ...new Map(
      produits
        .filter((p) => p.categorie)
        .map((p) => [p.categorie.id, p.categorie])
    ).values(),
  ];

  const produitsAffiches = idCategorie === null
    ? produits
    : produits.filter((p) => p.categorie?.id === idCategorie);

  function choisirCategorie(id) {
    setSearchParams(id === null ? {} : { categorie: id });
  }

  return (
    <>
      <h1 className="visually-hidden">La boutique</h1>

      <div className="catalogue-filtres">
        <div className="catalogue-filtres-contenu">
          <div className="catalogue-categories" role="group" aria-label="Filtrer par catégorie">
            <button type="button" aria-pressed={idCategorie === null} onClick={() => choisirCategorie(null)}>
              Tout
            </button>
            {categories.map((c) => (
              <button key={c.id} type="button" aria-pressed={idCategorie === c.id} onClick={() => choisirCategorie(c.id)}>
                {c.nomCategorie}
              </button>
            ))}
          </div>
          <p className="catalogue-compteur" aria-live="polite">
            {produitsAffiches.length} {produitsAffiches.length > 1 ? 'pièces' : 'pièce'}
          </p>
        </div>
      </div>

      <div className="catalogue">
        {produitsAffiches.length === 0 ? (
          <p className="catalogue-vide">Aucune pièce dans cette catégorie.</p>
        ) : (
          <ul className="catalogue-grille">
            {produitsAffiches.map((produit) => {
              const infosPrix = resumePrix(produit.declinaisons);
              return (
                <li key={produit.id}>
                  <ProduitCard
                    id={produit.id}
                    nomDuProduit={produit.nomDuProduit}
                    description={produit.description}
                    urlImage={urlImageProduit(produit)}
                    prix={infosPrix?.min}
                    prixDes={infosPrix?.plusieursPrix}
                  />
                </li>
              );
            })}
          </ul>
        )}
      </div>
    </>
  );
};

export default Produits;