import { versLignePanier } from './versLignePanier.js';

const vase = {
  nomDuProduit: 'Vase en grès',
  images: [],
  declinaisons: [{ id: 4, prix: '32.00', stock: 5, couleur: { choixCouleur: 'Blanc' } }],
};
const bougie = {
  nomDuProduit: 'Bougie Lavande',
  images: [{ urlImage: 'https://res.cloudinary.com/exemple.png' }],
  declinaisons: [{ id: 1, prix: '14.90', stock: 25 }], // pas de couleur
};

console.log(versLignePanier(vase, vase.declinaisons[0]));
console.log(versLignePanier(bougie, bougie.declinaisons[0]));