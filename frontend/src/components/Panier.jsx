import { Container, Button, Table, Alert } from 'react-bootstrap';
import { Link } from 'react-router-dom';
import { usePanier } from '../hooks/usePanier';
import { formaterPrix } from '../utils/formaterPrix';
import { useState } from 'react';

const Panier = () => {
  // Données et actions du panier
  const { isEmpty, supprimer, changerQuantite, items, vider, cartTotal } = usePanier();

  const [erreur, setErreur] = useState(null);
  // Si le panier est vide : message + bouton vers la boutique
  if (isEmpty) {
    return (
      <Container className="py-5 text-center">
        <h1 className="mb-4">Votre panier</h1>
        {erreur && <Alert variant="danger">{erreur}</Alert>}
        <p className="text-muted">Votre panier est vide.</p>
        <Button as={Link} to="/produits" variant="danger">
          Découvrir la boutique
        </Button>
      </Container>
    );
  }

  async function executer(action) {
    setErreur(null);
    try { await action(); } catch (err) { setErreur(err.message); }
  }
  // Si le panier contient des articles : affichage du tableau
  return (
    <Container className="py-5">
      <h1 className="mb-4">Votre panier</h1>
      {erreur && <Alert variant="danger">{erreur}</Alert>}
      <Table responsive className="align-middle">
        <thead>
          <tr>
            <th>Article</th>
            <th>Prix</th>
            <th>Quantité</th>
            <th>Sous-total</th>
            <th><span className="visually-hidden">Actions</span></th>
          </tr>
        </thead>
        <tbody>
          {/* Une ligne <tr> par article */}
          {items.map((item) => (
            <tr key={item.id}>
              <td>
                <div className="d-flex align-items-center">
                  {/*L'image, seulement s'il y en a une */}
                  {item.image && (
                    <img
                      src={item.image}
                      alt={item.nom}
                      width={60}
                      className="me-3 rounded"
                    />
                  )}
                  <div>
                    <strong>{item.nom}</strong>
                    {/* La couleur en petit, seulement s'il y en a une */}
                    {item.couleur && (
                      <div className="text-muted small">{item.couleur}</div>
                    )}
                  </div>
                </div>
              </td>
              <td>{formaterPrix(item.price)}</td>
              <td>
                <div className="d-flex align-items-center gap-2">
                  <Button
                    size="sm"
                    variant="outline-secondary"
                    onClick={() => executer(() => changerQuantite(item.id, item.quantity - 1))}
                    disabled={item.quantity <= 1}
                    aria-label="Diminuer la quantité"
                  >
                    −
                  </Button>
                  <span>{item.quantity}</span>
                  <Button
                    size="sm"
                    variant="outline-secondary"
                    onClick={() => executer(() => changerQuantite(item.id, item.quantity + 1))}
                    disabled={item.quantity >= item.stock}
                    aria-label="Augmenter la quantité"
                  >
                    +
                  </Button>
                </div>
              </td>
              <td>{formaterPrix(item.itemTotal)}</td>
              <td>
                <Button variant="link" className="text-danger" onClick={() => executer(() => supprimer(item.id))}>
                  Supprimer
                </Button>
              </td>
            </tr>
          ))}
        </tbody>
      </Table>
      <div className="d-flex justify-content-between align-items-center border-top pt-3 mt-3">
        <Button variant="outline-danger" onClick={() => executer(vider)}>
          Vider le panier
        </Button>
        <div className="text-end">
          <p className="fs-4 mb-2">
            Total : <strong>{formaterPrix(cartTotal)}</strong>
          </p>
          <Button as={Link} to="/commande" variant="danger">
            Commander
          </Button>
        </div>
      </div>
    </Container>
  );
};

export default Panier;