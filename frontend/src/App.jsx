import { BrowserRouter, Routes, Route } from 'react-router-dom';
import 'bootstrap/dist/css/bootstrap.min.css';
import './index.css';

import Accueil from './pages/Accueil.jsx';
import Panier from './pages/Panier.jsx';
import TopBar from "./components/TopBar.jsx";
import Produits from './pages/Produits.jsx';
import Footer from "./components/Footer.jsx";
import ProduitDetail from './pages/ProduitDetail.jsx';
import Connexion from './pages/Connexion.jsx';
import Commande from './pages/Commande.jsx';
import Confirmation from './pages/Confirmation.jsx';
import Inscription from './pages/Inscription.jsx';


function App() {
    return (
        <BrowserRouter>
            <TopBar />
            <main className="flex-grow-1">
                <Routes>
                    <Route path="/" element={<Accueil />} />
                    <Route path="/panier" element={<Panier />} />
                    <Route path="/produits" element={<Produits />} />
                    <Route path="/produits/:id" element={<ProduitDetail />} />
                    <Route path="/connexion" element={<Connexion />} />
                    <Route path="/commande" element={<Commande />} />
                    <Route path="/commande/confirmation" element={<Confirmation />} />
                    <Route path="/inscription" element={<Inscription />} />

                    {/* Route attrape-tout pour éviter la page blanche sur des liens non définis (ex: /connect, /contact) */}
                    <Route path="*" element={<p className="text-center py-5">Page introuvable</p>} />
                </Routes>
            </main>
            <Footer />
        </BrowserRouter>
    );
}

export default App;