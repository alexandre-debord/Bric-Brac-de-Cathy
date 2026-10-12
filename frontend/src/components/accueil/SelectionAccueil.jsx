import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { apiFetch } from '../../utils/apiFetch';
import { urlImageProduit } from '../../utils/urlImageProduit';
import '../../css/SelectionAccueil.css';

const NB_PRODUITS = 6;
const NB_MAX = 6;
const TAILLE_MOTIF = 3;

const INDEX_GRANDES = [0, 3]; // cartes qui prennent 2 rangées

export default function SelectionAccueil() {          // ← manquant
    const [produits, setProduits] = useState([]);

    useEffect(() => {
        let annule = false;
        apiFetch('/api/produits')
            .then((data) => {
                const nb = Math.floor(Math.min(data.length, NB_MAX) / TAILLE_MOTIF) * TAILLE_MOTIF;
                if (!annule) setProduits(data.slice(0, nb));
            })
            .catch(() => { /* section décorative : en cas d'erreur, on ne l'affiche pas */ });
        return () => { annule = true; };
    }, []);

    if (produits.length === 0) return null; // APRÈS les hooks

    return (
        <section className="selection">
            <div className="selection-entete">
                <div>
                    <p className="libelle">Le Cabinet de Curiosités</p>
                    <h2 className="titre-serif selection-titre">
                        De petits objets, <em>de grands trésors.</em>
                    </h2>
                </div>
                <p className="selection-intro">
                    Une sélection d'objets uniques et insolites dénichés au fil de nos trouvailles.
                </p>
            </div>

            <ul className="selection-grille">
                {produits.map((produit, index) => {
                    const urlImage = urlImageProduit(produit);
                    const grande = index % TAILLE_MOTIF === 0;
                    return (
                        <li key={produit.id} className={`selection-carte${grande ? ' selection-carte--grande' : ''}`}>
                            <Link to={`/produits/${produit.id}`} className="selection-lien">
                                {urlImage && <img src={urlImage} alt="" />}
                                <span className="selection-nom">{produit.nomDuProduit}</span>
                            </Link>
                        </li>
                    );
                })}
            </ul>
        </section>
    );
}
