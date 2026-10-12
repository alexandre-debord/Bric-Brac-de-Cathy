import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiFetch } from '../utils/apiFetch';
import { melanger } from '../utils/melanger';
import { urlImageProduit } from '../utils/urlImageProduit';
import { resumePrix } from '../utils/resumePrix';
import { formaterPrix } from '../utils/formaterPrix';
import '../css/SuggestionsProduits.css';

const NB_SUGGESTIONS = 3;

export default function SuggestionsProduits({ idActuel }) {
  const [produits, setProduits] = useState([]);

  useEffect(() => {
    let annule = false;
    apiFetch('/api/produits')
      .then((data) => {
        const autres = data.filter((p) => p.id !== idActuel);   // pas le produit affiché
        if (!annule) setProduits(melanger(autres).slice(0, NB_SUGGESTIONS));
      })
      .catch((err) => console.error('Suggestions :', err));
    return () => { annule = true; };
  }, [idActuel]);

  if (produits.length === 0) return null;

  return (
    <section className="suggestions" aria-labelledby="titre-suggestions">
      <div className="suggestions-entete">
        <h2 id="titre-suggestions" className="titre-serif suggestions-titre">
          Vous aimerez aussi <em>le reste de la collection</em>
        </h2>
        <Link to="/produits" className="suggestions-voir-tout">Voir tout</Link>
      </div>

      <ul className="suggestions-liste">
        {produits.map((produit) => {
          const urlImage = urlImageProduit(produit);
          const infosPrix = resumePrix(produit.declinaisons);
          return (
            <li key={produit.id}>
              <Link to={`/produits/${produit.id}`} className="suggestion-carte">
                <div className="suggestion-image">
                  {urlImage && <img src={urlImage} alt="" />}
                </div>
                <span className="suggestion-nom">{produit.nomDuProduit}</span>
                {infosPrix && (
                  <span className="suggestion-prix">
                    {infosPrix.plusieursPrix && 'dès '}{formaterPrix(infosPrix.min)}
                  </span>
                )}
              </Link>
            </li>
          );
        })}
      </ul>
    </section>
  );
}