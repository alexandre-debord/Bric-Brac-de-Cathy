  // Mélange de Fisher-Yates : chaque ordre a exactement la même probabilité
export function melanger(tableau) {
  const copie = [...tableau];               // on ne modifie pas l'original
  for (let i = copie.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [copie[i], copie[j]] = [copie[j], copie[i]];   // échange de deux cases
  }
  return copie;
}