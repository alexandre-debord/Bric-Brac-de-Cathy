import { useCart } from 'react-use-cart';

export function usePanier() {
  const { addItem, updateItemQuantity, getItem, ...reste } = useCart();

  function ajouter(declinaison, quantite = 1) {
    // Garde : valide que quantite est bien un entier >= 1
    if (!Number.isInteger(quantite) || quantite < 1) return false;

    const id = String(declinaison.id_declinaison);
    const dejaDansPanier = getItem(id)?.quantity ?? 0;
    const possible = Math.min(quantite, declinaison.stock - dejaDansPanier);

    if (possible <= 0) return false;

    addItem(
      {
        id,
        price: declinaison.prix,
        nom: declinaison.nom,
        couleur: declinaison.couleur,
        image: declinaison.image,
        stock: declinaison.stock,
      },
      possible
    );
    return true;
  }

  function changerQuantite(id, quantite) {
    if (!Number.isInteger(quantite) || quantite < 1) return;

    const idString = String(id);
    const ligne = getItem(idString);
    if (!ligne) return;

    const cible = Math.min(quantite, ligne.stock);
    updateItemQuantity(idString, cible);
  }

  return { ajouter, changerQuantite, ...reste };
}