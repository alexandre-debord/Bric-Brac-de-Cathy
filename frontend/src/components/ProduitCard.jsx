import { Plus } from 'react-bootstrap-icons';
import { Link } from 'react-router-dom';
import { formaterPrix } from '../utils/formaterPrix';
import '../css/Produits.css';


export default function ProduitCard({ id, nomDuProduit, description, prix, prixDes, urlImage }) {


  return (
    <div className="product-card">
      <Link to={`/produits/${id}`} className="product-card-lien">
        {urlImage && <img src={urlImage} alt="" className="product-card-img" />}
        {prix != null && (
          <span className="product-card-price-badge">
            {prixDes && 'dès '}{formaterPrix(prix)}
          </span>
        )}
        <div className="product-card-overlay">
          <h3 className="product-card-title">{nomDuProduit}</h3>
          <p className="product-card-subtitle">{description}</p>
        </div>
        <span className="product-card-add-btn" aria-hidden="true">
          <Plus size={20} />
        </span>
      </Link>
    </div>
  );
}
