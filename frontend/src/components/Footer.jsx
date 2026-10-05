import React from 'react';
import { Container, Row, Col, Nav } from 'react-bootstrap';
import { Envelope, Instagram } from 'react-bootstrap-icons';
import { NavLink } from "react-router-dom";

import '../css/Footer.css';

const Footer = () => {
  return (
    <footer className="footer-custom">
      <Container>
        <Row className="gy-4 gx-lg-5 mb-5 align-items-start">

          {/* Colonne 1 : Marque et Description */}
          <Col lg={5} md={12} className="text-start">
            <h2 className="footer-title-main">Le Bric à Brac</h2>
            <h3 className="footer-title-sub">de Cathy</h3>
            <p className="footer-text">
              Bijoux et petits articles artisanaux, façonnés à la main dans l'atelier de Cathy. Chaque pièce est une édition unique.
            </p>
          </Col>

          {/* Colonne 2 : Navigation */}
          <Col lg={3} md={6} className="text-start">
            <h6 className="footer-section-title">NAVIGATION</h6>
            <Nav className="footer-nav-list flex-column">
                <Nav.Link className="footer-link" as={NavLink} to="/Boutique" >
                  La Boutique
                </Nav.Link>
                <Nav.Link className="footer-link" as={NavLink} to="/Atelier" >
                  L'Atelier
                </Nav.Link>
                <Nav.Link className="footer-link" as={NavLink} to="/" >
                  Accueil
                </Nav.Link>
            </Nav>
          </Col>

          {/* Colonne 3 : Contact */}
          <Col lg={4} md={6} className="text-start">
            <h6 className="footer-section-title">RESTONS EN CONTACT</h6>
            <div>
              <div className="footer-contact-item">
                <Envelope className="footer-icon" size={14} />
                <a href="mailto:contact@bricabraccathy.fr" className="footer-link">
                  contact@bricabraccathy.fr
                </a>
              </div>
              <div className="footer-contact-item text-center">
                <Instagram className="footer-icon" size={14} />
                <a href="https://instagram.com/bricabraccathy" target="_blank" rel="noreferrer" className="footer-link">
                  @bricabraccathy
                </a>
              </div>
              <p className="footer-contact-note">
                Commandes sur-mesure et créations personnalisées sur demande.
              </p>
            </div>
          </Col>

        </Row>

        {/* Bas de page */}
        <Row className="footer-bottom-row">
          <Col md={6} className="text-start mb-2 mb-md-0">
            © Le Bric à Brac de Cathy. Tous droits réservés.
          </Col>
        </Row>
      </Container>
    </footer>
  );
};

export default Footer;