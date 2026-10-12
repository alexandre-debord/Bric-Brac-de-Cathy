import imagePresentation from "../../assets/PresentationAccueil.jpg"; 
import '../../css/PresentationAccueil.css';

export default function PresentationAccueil() {
    return (
        <section className="presentation">
            <div className="presentation-image">
                <img src={imagePresentation} alt="Bague dorée suspendue à une chaîne, devant un livre ouvert et des feuilles d'automne" />
            </div>

            <div className="presentation-texte">
                <p className="libelle presentation-surtitre">Le Geste de l'Artisane</p>
                <h2 className="presentation-titre">
                    La matière entre les mains, <em>le temps qui s'attarde.</em>
                </h2>
                <p className="presentation-paragraphe">Dans son atelier, Cathy sélectionne chaque perle, tord chaque fil,
                    assemble chaque couleur. Rien n'est laissé au hasard —
                    seulement au temps, à la patience et à l'envie de créer quelque
                    chose de profondément personnel.</p>
            </div>
        </section>
    );
}