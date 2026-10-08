export class ErreurApi extends Error {
  constructor(message, status) {
    super(message);        // donne le message à la classe Error parente
    this.status = status;  // 0 = serveur injoignable
  }
}

export async function apiFetch(chemin, { method = 'GET', corps, token } = {}) {
  const headers = {};
  if (corps !== undefined) headers['Content-Type'] = 'application/json';
  if (token) headers.Authorization = `Bearer ${token}`;

  let reponse;
  try {
    reponse = await fetch(`${import.meta.env.VITE_API_URL}${chemin}`, {
      method,
      headers,
      body: corps !== undefined ? JSON.stringify(corps) : undefined,
    });
  } catch {
    // fetch ne rejette QUE si la requête n'a pas pu partir ou aboutir (serveur arrêté, CORS…)
    throw new ErreurApi('Impossible de contacter le serveur.', 0);
  }

  // Certaines réponses n'ont pas de corps JSON : on ne plante pas pour autant
  const data = await reponse.json().catch(() => null);

  if (!reponse.ok) {
    throw new ErreurApi(data?.error ?? 'Erreur serveur, réessaie plus tard.', reponse.status);
  }
  return data;
}