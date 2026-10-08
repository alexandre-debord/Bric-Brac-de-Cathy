import { useCart } from "react-use-cart";
import { useAuth } from "../context/AuthContext";
import { lignesServeurVersItems } from "../utils/panierServeur";
import { useApi } from "./useApi";

export function usePanier() {
  const {
    addItem,
    updateItemQuantity,
    getItem,
    setItems,
    removeItem,
    emptyCart,
    ...reste
  } = useCart();
  const { estConnecte } = useAuth();
  const api = useApi();
  // Appelle une route du panier, puis remplace le panier local par la réponse du serveur

  async function appelPanier(chemin, method, corps) {
    const panier = await api(chemin, { method, corps });
    setItems(lignesServeurVersItems(panier.lignes));
  }

  async function ajouter(declinaison, quantite = 1) {
    // Garde : valide que quantite est bien un entier >= 1
    if (!Number.isInteger(quantite) || quantite < 1) return false;

    if (estConnecte) {
      await appelPanier("/api/panier/lignes", "POST", {
        id_declinaison: declinaison.id_declinaison,
        quantite: quantite,
      });
      return true;
    }

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
      possible,
    );
    return true;
  }

  async function changerQuantite(id, quantite) {
    if (!Number.isInteger(quantite) || quantite < 1) return;

    if (estConnecte) {
      await appelPanier(`/api/panier/lignes/${id}`, "PATCH", { quantite });
      return;
    }

    const idString = String(id);
    const ligne = getItem(idString);
    if (!ligne) return;

    const cible = Math.min(quantite, ligne.stock);
    updateItemQuantity(idString, cible);
  }

  async function supprimer(id) {
    if (estConnecte) {
      await appelPanier(`/api/panier/lignes/${id}`, "DELETE");
      return;
    }
    removeItem(id);
  }

  async function vider() {
    if (estConnecte) {
      await appelPanier(`/api/panier`, "DELETE");
      return;
    }
    emptyCart();
  }

  return { ajouter, changerQuantite, supprimer, vider, ...reste };
}
