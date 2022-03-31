INSTALLATION
-----------------
Le module Discount Generator suit une procédure standard d'installation, commune à tous les modules Prestashop. Si vous avez plusieurs boutiques sur votre site, n'oubliez pas d'aller dans les paramètres du module (en cliquant sur le bouton "Configurer") et cocher l'option "Activer le module pour ce contexte de boutiques: all shops". Plus tard, cette option vous permettra de générer des codes soit séparément (pour l'une de vos boutiques), soit pour l'ensemble de vos boutiques. Si vous souhaitez suspendre l'usage du module, il suffit de le désactiver en utilisant le bouton "Désactiver".

Attention : Si pour une raison ou une autre, vous souhaitez supprimer complètement le module de la liste des modules, il est important de le DÉSINSTALLER D'ABORD (bouton "Désinstaller") et ne le SUPPRIMER qu'ENSUITE (bouton "Supprimer"). Cet ordre d'actions garantira un nettoyage complet des tableaux de votre base des données. 

Attention : comme le module Discount Generator étend la fonctionnalité du panier, il faut désactiver les options "Désactiver toutes les surcharges" et "Désactiver les modules non développés par PrestaShop" dans la section "Paramètres avancés" > "Performances" > "Mode debug".

DESCRIPTION
-----------------
Le module Discount Generator permet de générer en un seul clic d'importantes quantités des codes de réduction ayant chacun son code unique. 
Vous pouvez générer des codes pour un produit spécifique, un groupe de produits, une catégorie spécifique, ou une commande entière : toutes les conditions standard de règles panier sont à votre disposition.

Vous allez pouvoir déterminer : 
- le nombre de bons de réduction à code unique à générer 
- la structure du code unique qui sera composé de lettres et/ou chiffres aléatoires 
- le nombre d'utilisations maximum par bon de réduction
- le nombre d'utilisations maximum par utilisateur

PARAMETRAGE DU MODULE
------------------
Le module Discount Generator s'installe directement dans le menu Catalogue > Réductions et enrichit les fonctions natives de cette rubrique. 

1. Dans le Prestashop 1.7, dans le menu, choisissez Catalogue > Réductions et сliquez ensuite soit sur le bouton "Ajouter une règle". Dans le Prestashop 1.5 - 1.6, dans le menu, choisissez Promotions > Règles panier et сliquez ensuite soit sur le bouton "Ajouter une règle".
2. Cochez la case "Générer avec Discount Generator" et remplissez les champs qui apparaîtront sur la page. 
3. Remplissez tous les champs obligatoires : 
   - Nombre total de bons de réduction à générer : Précisez combien de bons de réduction vous souhaitez générer.
   - Structure des bons de réduction, qui est composée de :
	  - Préfixe : Cela peut être le nom de votre société ou le nom de la campagne publicitaire. C'est une partie stable de la structure : elle se répétera dans tous les bons générés. Les caractères suivants ne sont pas autorisés : ^!,;?=+()@"°{}_$%
	  - Masque du code : une combinaison de chiffres et de lettres qui seront générés de façon aléatoire. X indiquera un chiffre, Y une lettre de l'alphabet latin standard (sans accents). Si votre préfixe est TEST- et votre masque est XXYY , le module générera des codes uniques de ce type : TEST-96FA, TEST-27ME etc.
4. Avant de valider, merci de vérifier si tous les champs obligatoires ont été renseignés.
5. Validez en cliquant sur le bouton "Enregistrer". Le module va générer le nombre de bons que vous avez déterminé. 

EXPORT DES CODES GÉNÉRES
----------------------
Le tableau "Historique du module Discount Generator", qui se trouve en bas de la page de configuration du module, vous permettra d'exporter les codes générés avec Discount Generator.
 
Vous trouverez trois types de listes :

- "Tous" - la liste de tous les bons générés au même moment, leur date de début et d'expiration, leur type de réduction. Cette liste est générée une fois, au moment de la création des bons, et ne sera pas mise à jour ultérieurement.
- "Utilisés" - la liste des bons qui ont déjà été utilisés par vos clients, en précisant le nom et l'adresse email du client. Cette une liste dynamique qui se met à jour à chaque téléchargement. 
- "Non utilisés" - la liste des bons qui N'ONT PAS été encore utilisés. Cette une liste dynamique qui se met à jour à chaque téléchargement. 

CONTACT
-----------------
SAV : Merci de nous contacter depuis votre interface Addons pour faciliter l'identification de votre commande : https://addons.prestashop.com/fr/historique-des-commandes.