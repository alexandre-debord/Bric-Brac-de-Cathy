export function resumePrix(declinaisons) {
  if (!declinaisons || declinaisons.length === 0) return null;
  const prix = declinaisons.map((d) => Number(d.prix));
  const min = Math.min(...prix);
  const plusieursPrix = prix.some((p) => p !== min);
  return { min, plusieursPrix };
}