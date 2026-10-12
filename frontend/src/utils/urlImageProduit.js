export function urlImageProduit(produit) {
  return produit.images?.[0]?.urlImage ?? null;
}