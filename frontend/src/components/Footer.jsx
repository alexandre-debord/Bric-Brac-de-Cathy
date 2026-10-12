import React from 'react';
import { Container, Row, Col, Nav } from 'react-bootstrap';
import { Envelope, Instagram } from 'react-bootstrap-icons';
import { NavLink } from 'react-router-dom';

import '../css/Footer.css';

const Footer = () => {
  return (
    <footer className="footer-custom">
      <Container>
        <Row className="gy-4 gx-lg-5 mb-5 align-items-start">

          {/* Marque et description */}
          
          <Col lg={5} md={12}>
            <p className="footer-marque">
              Le Bric à Brac <em>de Cathy</em>
            </p>
            <p className="footer-text">
              Bijoux et petits articles artisanaux, façonnés à la main dans l'atelier de Cathy. Chaque pièce est une édition unique.
            </p>
          </Col>

          {/*Navigation */}

          <Col lg={3} md={6}>
            <h2 className="libelle">Navigation</h2>
            <Nav className="footer-nav-list flex-column">
              <Nav.Link className="footer-link" as={NavLink} to="/">
                Accueil
              </Nav.Link>
              <Nav.Link className="footer-link" as={NavLink} to="/produits">
                La Boutique
              </Nav.Link>
            </Nav>
          </Col>

          {/* Contact */}

          <Col lg={4} md={6}>
            <h2 className="libelle">Restons en contact</h2>
            <div className="footer-contact-item">
              <Envelope className="footer-icon" size={14} aria-hidden="true" />
              <a href="mailto:contact@bricabraccathy.fr" className="footer-link">
                contact@bricabraccathy.fr
              </a>
            </div>
            <div className="footer-contact-item">
              <Instagram className="footer-icon" size={14} aria-hidden="true" />
              <a href="https://instagram.com/bricabraccathy" target="_blank" rel="noreferrer" className="footer-link">
                @bricabraccathy
              </a>
            </div>
            <p className="footer-contact-note">
              Commandes sur-mesure et créations personnalisées sur demande.
            </p>
          </Col>

        </Row>

        {/* Bas de page */}

        <Row className="footer-bottom-row">
          <Col md={6} className="mb-2 mb-md-0">
            © Le Bric à Brac de Cathy. Tous droits réservés.
          </Col>
        </Row>
      </Container>
    </footer>
  );
};

export default Footer;