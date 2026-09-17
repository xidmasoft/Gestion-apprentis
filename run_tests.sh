#!/bin/bash
echo "=== TESTS FONCTIONNELS ET API ==="

# --- MODIFICATION ---
echo "8. Modification - valide :"
ID_TO_MOD=$(curl -s -X GET http://localhost:8000/api/apprenants.php?search=TEST001 | grep -o '"id":[0-9]*' | head -n 1 | grep -o '[0-9]*')
echo "ID_TO_MOD is $ID_TO_MOD"
curl -s -X PUT -H "Content-Type: application/json" -d "{\"id\": $ID_TO_MOD, \"matricule\": \"TEST001\", \"nom\": \"DoeMod\", \"prenom\": \"John\", \"date_naissance\": \"2000-01-01\", \"sexe\": \"M\", \"filiere_id\": 1, \"niveau_id\": 1, \"annee_id\": 1, \"statut\": \"Inscrit\"}" http://localhost:8000/api/apprenants.php
echo -e "\n"

# --- SUPPRESSION ---
echo "10. Suppression - valide :"
curl -s -X DELETE -H "Content-Type: application/json" -d "{\"id\": $ID_TO_MOD}" http://localhost:8000/api/apprenants.php
echo -e "\n"
