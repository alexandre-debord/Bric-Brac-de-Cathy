import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import App from './App'
import { CartProvider } from 'react-use-cart'

// Nettoie un panier corrompu AVANT que CartProvider ne le lise
// (la bibliothèque fait un JSON.parse sans protection au démarrage).
try {
  const brut = localStorage.getItem('react-use-cart')
  if (brut) {
    const donnees = JSON.parse(brut)
    if (!Array.isArray(donnees?.items)) throw new Error('Panier corrompu')
  }
} catch {
  try {
    localStorage.removeItem('react-use-cart')
  } catch {
    // Stockage bloqué par le navigateur : rien à nettoyer, la bibliothèque gère ce cas.
  }
}

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <CartProvider>
      <App />
    </CartProvider>
  </StrictMode>,
)