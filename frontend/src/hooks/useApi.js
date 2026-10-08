import { useCallback } from 'react';
import { useAuth } from '../context/AuthContext';
import { apiFetch, ErreurApi } from '../utils/apiFetch';

export function useApi() {
  const { token, deconnexion } = useAuth();

  return useCallback(async (chemin, options = {}) => {
    try {
      return await apiFetch(chemin, { ...options, token });
    } catch (err) {
      if (err.status === 401) {
        deconnexion();
        throw new ErreurApi('Ta session a expiré, reconnecte-toi.', 401);
      }
      throw err;
    }
  }, [token, deconnexion]);
}