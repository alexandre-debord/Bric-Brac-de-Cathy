// Serveur → local : format attendu par setItems de react-use-cart
export function lignesServeurVersItems(lignes) {
  return lignes.map((l) => ({
    id: String(l.id_declinaison),
    price: l.prix,
    quantity: l.quantite,
    nom: l.nom,
    couleur: l.couleur,
    image: l.image,
    stock: l.stock,
  }));
}

// Local → serveur : format attendu par ta route fusion
export function itemsVersLignesServeur(items) {
  return items.map((i) => ({
    id_declinaison: Number(i.id),
    quantite: i.quantity,
  }));
}