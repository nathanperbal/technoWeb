document.addEventListener("DOMContentLoaded", function () {
    var fleche = document.getElementById("fleche");
    var deconnexion = document.getElementById("deconnexion");

    // Fonction pour basculer l'affichage du bouton de déconnexion
    function toggleDeconnexion() {
        deconnexion.style.display = (deconnexion.style.display === "block") ? "none" : "block";
    }

    // Ajouter un écouteur d'événement pour le clic sur la flèche
    fleche.addEventListener("click", function () {
        toggleDeconnexion();
    });

    // Ajouter un écouteur d'événement pour le clic sur le lien de déconnexion
    document.getElementById("deconnexion-link").addEventListener("click", function (event) {
        event.preventDefault();
        document.getElementById("deconnexion-form").submit();
    });
});


//Exam
 function validateForm() {
        var radioButtons = document.getElementsByName('reponse');
        var isAnswerSelected = false;

        for (var i = 0; i < radioButtons.length; i++) {
            if (radioButtons[i].checked) {
                isAnswerSelected = true;
                break;
            }
        }

        if (!isAnswerSelected) {
            alert("Veuillez selectionner une reponse ou indiquer 'Je ne sais pas'");
            return false; // Empêche la soumission du formulaire
        }

        return true; // Permet la soumission du formulaire
    }
