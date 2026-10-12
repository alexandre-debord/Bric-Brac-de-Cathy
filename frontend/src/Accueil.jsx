 import { Container, Row, Col, Nav, Offcanvas } from 'react-bootstrap';
 import HeroAccueil from './components/accueil/HeroAccueil';
 import SelectionAccueil from './components/accueil/SelectionAccueil';
 import PresentationAccueil from './components/accueil/PresentationAccueil';
 
export default function Accueil() {
  return (
    <>
      <HeroAccueil />
      <SelectionAccueil />
      <PresentationAccueil />
    </>
  );
}
