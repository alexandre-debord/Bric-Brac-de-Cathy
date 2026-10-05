
const formateur = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' });

export function formaterPrix(montant) {
  return formateur.format(montant);
}