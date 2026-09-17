#!/bin/bash
echo "=== TESTS SECURITE ET BDD ==="

echo "1. Injection SQL dans la recherche :"
curl -s -X GET "http://localhost:8000/api/apprenants.php?search=';%20DROP%20TABLE%20apprenants;--"
echo -e "\n"

echo "2. Injection XSS dans la création :"
curl -s -X POST -H "Content-Type: application/json" -d '{"matricule": "XSS001", "nom": "<script>alert(1)</script>", "prenom": "XSS", "date_naissance": "2000-01-01", "sexe": "M", "filiere_id": 1, "niveau_id": 1, "annee_id": 1}' http://localhost:8000/api/apprenants.php
echo -e "\n"
ID_XSS=$(curl -s -X GET http://localhost:8000/api/apprenants.php?search=XSS001 | grep -o '"id":[0-9]*' | head -n 1 | grep -o '[0-9]*')
echo "Verif bdd :"
mysql -u jules -ppassword gestion_apprenants -e "SELECT nom FROM apprenants WHERE id=$ID_XSS"

echo -e "\n3. Suppression de la donnée injectée :"
curl -s -X DELETE -H "Content-Type: application/json" -d "{\"id\": $ID_XSS}" http://localhost:8000/api/apprenants.php
echo -e "\n"

echo "4. Test intégrité référentielle (filière non existante) :"
curl -s -X POST -H "Content-Type: application/json" -d '{"matricule": "TEST_INT", "nom": "A", "prenom": "B", "date_naissance": "2000-01-01", "sexe": "M", "filiere_id": 99999, "niveau_id": 1, "annee_id": 1}' http://localhost:8000/api/apprenants.php
echo -e "\n"
