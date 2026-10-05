import React, { useState } from 'react';
import { Container, Row, Col, Nav, Offcanvas } from 'react-bootstrap';
import { List, Bag } from 'react-bootstrap-icons';
import { NavLink } from 'react-router-dom';
import { usePanier } from '../hooks/usePanier';

import '../css/TopBar.css';

const TopBar = () => {
    const [showMenu, setShowMenu] = useState(false);
    const { totalItems } = usePanier();

    const handleClose = () => setShowMenu(false);
    const handleShow = () => setShowMenu(true);

    return (
        <>
            <header className="topbar-custom">
                <Container fluid className="px-3 px-md-4">
                    <Row className="align-items-center">

                        {/* Gauche : Bouton MENU (Visible UNIQUEMENT sur mobile/tablette) */}
                        <Col xs={3} md={4} className="d-flex align-items-center">
                            <button
                                className="topbar-menu-btn d-md-none"
                                type="button"
                                aria-label="Ouvrir le menu"
                                onClick={handleShow}
                            >
                                <List size={22} />
                                <span className="d-none d-sm-inline ms-1">MENU</span>
                            </button>
                        </Col>

                        {/* Centre : Titre de la marque */}
                        <Col xs={6} md={4} className="text-center">
                            <Nav.Link className="topbar-brand" as={NavLink} to="/">
                                Le Bric à Brac de Cathy
                            </Nav.Link>
                        </Col>

                        {/* Droite : Liens */}
                        <Col xs={3} md={4}>
                            <div className="topbar-right">
                                <Nav.Link className="topbar-link d-none d-md-inline" as={NavLink} to="/produits">
                                    La Boutique
                                </Nav.Link>
                                <Nav.Link className="topbar-link d-none d-md-inline" as={NavLink} to="/connect">
                                    Se Connecter
                                </Nav.Link>

                                {/* Lien Panier : Compteur toujours visible + aria-label pour l'accessibilité */}
                                <Nav.Link 
                                    className="topbar-cart-link" 
                                    as={NavLink} 
                                    to="/panier"
                                    aria-label={`Panier, ${totalItems} article(s)`}
                                >
                                    <Bag size={18} />
                                    <span className="d-none d-sm-inline ms-1">Panier</span> ({totalItems})
                                </Nav.Link>
                            </div>
                        </Col>

                    </Row>
                </Container>
            </header>

            {/* Panneau latéral Burger */}
            <Offcanvas
                show={showMenu}
                onHide={handleClose}
                placement="start"
                className="offcanvas-custom"
            >
                <Offcanvas.Header closeButton className="px-4 pt-4">
                    <Offcanvas.Title className="text-uppercase fs-6 offcanvas-title-spaced text-muted">
                        Navigation
                    </Offcanvas.Title>
                </Offcanvas.Header>

                <Offcanvas.Body className="d-flex flex-column px-4 pb-4">
                    <ul className="offcanvas-menu-list">
                        <li className="offcanvas-menu-item">
                            <Nav.Link as={NavLink} to="/" className="offcanvas-menu-link" onClick={handleClose}>
                                Accueil
                            </Nav.Link>
                        </li>
                        <li className="offcanvas-menu-item">
                            <Nav.Link as={NavLink} to="/produits" className="offcanvas-menu-link" onClick={handleClose}>
                                La Boutique
                            </Nav.Link>
                        </li>
                        <li className="offcanvas-menu-item">
                            <Nav.Link as={NavLink} to="/connect" className="offcanvas-menu-link" onClick={handleClose}>
                                Se Connecter
                            </Nav.Link>
                        </li>
                        <li className="offcanvas-menu-item">
                            <Nav.Link as={NavLink} to="/contact" className="offcanvas-menu-link" onClick={handleClose}>
                                Contact
                            </Nav.Link>
                        </li>
                    </ul>

                    <div className="offcanvas-footer-info">
                        <p className="m-0">© Le Bric à Brac de Cathy</p>
                        <small>Créations artisanales & fait main</small>
                    </div>
                </Offcanvas.Body>
            </Offcanvas>
        </>
    );
};

export default TopBar;