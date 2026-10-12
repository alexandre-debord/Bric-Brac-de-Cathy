import { Link } from 'react-router-dom';
import imageHero from '../../assets/bijouxAccueil.png';
import '../../css/HeroAccueil.css';

export default function HeroAccueil() {
    return (
        <section className="hero">
            <div className="hero-texte">
                <p className="hero-surtitre libelle">Bijoux & petits articles artisanaux</p>
                <h1 className="hero-titre">
                    Le Bric<br />à Brac<br /><em>de Cathy</em>
                </h1>
                <p className="hero-intro">
                    Des petits trésors façonnés à la main, où chaque perle
                    et chaque fil racontent une histoire. Découvrez l'art de
                    l'unique.
                </p>
                <Link to="/produits" className="hero-bouton">Découvrir la boutique</Link>
            </div>

            <div className="hero-image">
                <img src={imageHero} alt="Bijoux de présentation" />
            </div>
        </section>
    );
}