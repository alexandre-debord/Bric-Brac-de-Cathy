import { Navigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext'; 
import { Spinner, Container } from 'react-bootstrap';


export default function RouteAdmin({ children }) {
    const { chargementUtilisateur, estAdmin, estConnecte } = useAuth();

    
    if (chargementUtilisateur) {
        return  (
      <Container className="text-center my-5">
        <Spinner animation="border" role="status">
          <span className="visually-hidden">Vérification de l’accès…</span>
        </Spinner>
      </Container>
    ); 
    }

    if (!estAdmin) {
        return <Navigate to="/" replace />;
    }


    return children;
}
