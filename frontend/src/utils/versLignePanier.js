// Convertit un produit + une déclinaison de l'API
// au format attendu par le hook usePanier.
export function versLignePanier(produit, declinaison) {
  return {
    id_declinaison: declinaison.id,
    nom:  produit.nomDuProduit,
    couleur:       declinaison.couleur?.choixCouleur ?? '',
    prix:           Number(declinaison.prix),
    image:          produit.images[0]?.urlImage ?? '',
    stock:          declinaison.stock,
  };
}