import React from 'react';
import { Plus } from 'react-bootstrap-icons';
import { useNavigate } from 'react-router-dom';
import '../css/Produits.css';
import { formaterPrix } from '../utils/formaterPrix';

const ProduitCard = ({ id, nomDuProduit, description, urlImage, prix }) => {
  const navigate = useNavigate();

  const handleOpenDetail = (e) => {
    e.stopPropagation();
    if (id) {
      navigate(`/produits/${id}`);
    }
  };

  return (
    <div className="product-card" onClick={handleOpenDetail}>
      {prix !== undefined && (
        <div className="product-card-price-badge">
          {formaterPrix(Number(prix))}
        </div>
      )}

      <img
        src={urlImage}
        alt={nomDuProduit}
        className="product-card-img"
      />

      <div className="product-card-overlay">
        <h4 className="product-card-title">{nomDuProduit}</h4>
        <p className="product-card-subtitle">{description}</p>
      </div>

      <button
        className="product-card-add-btn"
        aria-label={`Voir le détail de ${nomDuProduit}`}
        onClick={handleOpenDetail}
      >
        <Plus size={22} />
      </button>
    </div>
  );
};

export default ProduitCard;