=== WPCB ===Contributors: 6WWWDonate link: http://wpcb.fr/donate/Tags: wp-e-commerce, atos, sips, carte bancaire, wpcb, mercanet, 6WWW, mailchimpRequires at least: 2.7Tested up to: 3.3Stable tag: 2.1

Paiement par cartes bancaires (majoritée des banques françaises), paypal, chèques et virement pour le plugin WP e-Commerce.
Calcul de frais de port basé sur la poste (colis, chronopost, et d'autres à venir...)
== Description ==Paiement par cartes bancaires (majoritée des banques françaises), paypal, chèques et virement pour le plugin WP e-Commerce.
Calcul de frais de port basé sur la poste (colis, chronopost, et d'autres à venir...)
Fonctionne pour de nombreuses banques françaises :* Banque Populaire (CyberPlus, tm)* Société Générale (Sogenactif, tm)* Crédit Lyonnais (Sherlock, tm)* Crédit du Nord (Webaffaires, tm)* CCF (Elysnet, tm)* BNP (Mercanet, tm)
* et de nombreuses autres banques basée sur la technologie ATOS SIPS ou SYSTEMPAY CYBERPLUS

= Attention =
Version Beta !
La dernière version stable : http://downloads.wordpress.org/plugin/wpcb.1.1.9.zip

= Pour les détenteurs d'une clé API =
* Support pour la mise en place du plugin par email.
* Calcul des frais de port !
* Ajout dans google drive de toutes vos ventes !
* Ajout de tous vos acheteurs dans votre outil de mailling MailChimp
= A venir pour les détenteurs d'une clé API =
* Sauter l'étape de clic sur l'icone des cartes ou du bouton paypal (comme woocommerce)
== Installation ==1. Envoyer `wpcb` vers le dossier `/wp-content/plugins/`2. Activer le plugin dans le menu 'Extensions' de Wordpress3. Placer `[wpcb]` sur une (et une seule!) page
4. Régler les paramètres suivant les indications
5. Rendez-vous sur http://wpcb.fr/api-key pour débloquer les options pro

== Frequently Asked Questions ==

= Que faire des fichiers envoyé par ma banque ? =

Configurer correctement vos dossiers/fichiers obtenus par votre banque (dossier crypté)
Pour les banques ATOS SIPS : 
Dans le dossier cgi-bin (non visible depuis Internet) vous devez avoir :
* parcom.mercanet
* parcom.005009461540411 (votre numéro de marchand à la place de celui là) (le contenu du fichier n'a pas a être modifé)
* log.txt
* certif.fr.005009461540411 (votre numéro de marchand à la place de celui là)
* pathfile (à modifier suivant cet exemple !!! )
* request
* response
Note : les fichiers call_request.php, call_response.php et call_auto_response.php dans le package fourni par la banque ne sont pas necessaires car wpcb les remplace.

http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-14.20.411.png

= Pour les banques Systempay Cyberplus, comment faire ? =
Dans votre interface admin de gestion vad, régler : 
Url serveur : http://monsite.fr/?gateway=systempaycyberplus

= Comment activer/déscativer le paiement par carte bancaire ? =
Réglages > Boutique > Paiements
http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-17.30.06.png

= Comment personaliser la page des icones de cartes bancaires ? =
Créer une page WordPress avec le shortcode : '[wpcb]'.
Vous venez de créer la page qui affichera les icônes des cartes bleues une fois que le client aura cliqué sur Achat(Voir l'image ci-dessous, partie droite). Vous pouvez ajouter du texte comme bon vous semble sur cette page.

http://6www.net/blog/wp-content/uploads/2011/05/snap13-05-2011-12.30.5308-07-2011-17.24.50.png

= Comment identifier le reçu Systempay Cyberplus avec la commande wpec ? =
Le numéro de référence commande correspond au numéro de commande de wpec

= A quoi sert le mode test ? =
Le mode test permet de vérifier automatiquement le paiement sans passer par l'étape de saisie du numéro de carte bancaire. Il vous permet de vérifier que tout se passe bien dans votre processus.

= A quoi sert le mode demo ? =
Le mode demo permet d'utiliser le kit de démo fournit par votre banque. (Ne marche pas toujours très bien...)
= Y-a-t-il un mode debug ? =Oui, editer wp-config.php à la racine de votre site et mettez la variable globale WP_DEBUG = true . = Autre question ? =
Merci de poser vos questions sur le forum en cliquant à droite sur le bouton vert ->

Attention : Nous ne sommes pas responsable de la mauvaise utilisation du plugin WPCB mis à votre disposition gratuitement et toujours en phase d'amélioration. Vous l'utilisez en tout conscience et vous vous assurez de la protection de vos pages internet.

= Comment configurer la facture ? =
L'acheteur reçoit ensuite sa facture par email. Celle-ci peut être personnalisée dans Réglages > Boutique > Admin > Messages Personnalisés >Reçu d'achat

Exemple :
'Merci pour votre commande sur %shop_name%, vos courses vont vous être expédiées aussi vite que possible.

Numéro de commande : %purchase_id%
You ordered these items:
%product_list%%total_shipping%%total_price%
Les prix sont TTC.

A bientôt sur %shop_name%'

Note : La phrase : "L'opération a été effectuée avec succès" s'ajoute au début du message. L'objet de l'email est : "Reçu d'Achat"

= Que reçoit le vendeur ? =

Configuration du message de confirmation du règlement au vendeur

Le vendeur (vous) reçoit un email pour lui avertir qu'une commande a été réglée. Cet email se personnalise dans Réglages > Boutique > Admin > Messages Personalisés > Rapport d'administration

Exemple:

'Une commande vient d'être passée sur  le site %shop_name% !

Numéro de commande : %purchase_id%
%product_list%%total_shipping%%total_price%
Les prix sont TTC.
Note : les coordonnées de l'acheteur s'ajoute au dessus de ce message : Nom, Email, Coordonnées postales, etc.'
= Ou placer le fichier automatic_response.php ? =Ce fichier est automatiquement copié à la racine de votre blog wordpress c'est à dire à coté du fichier wp-config.phpSi cela n'est pas fait, faite le manuellement.= Comment configurer google drive pour recevoir les ventes ? =Télécharger le fichier <a href="https://docs.google.com/spreadsheet/ccc?key=0AkLWPxefL-fydHllcFJKTzFLaGdRUG5tbXM1dWJCVWc">https://docs.google.com/spreadsheet/ccc?key=0AkLWPxefL-fydHllcFJKTzFLaGdRUG5tbXM1dWJCVWc</a>Envoyer ce fichier dans votre google drive et noter votre nouvelle cle de fichier (dans mon fichier, à titre d'exemple, la clé est : 0AkLWPxefL-fydHllcFJKTzFLaGdRUG5tbXM1dWJCVWc cela se lit dans l'url)Ne changer pas les entetes et attention à ce que ce soit la feuille numéro 1 du classeur !!
= Autre question ? =Attention : Nous ne sommes pas responsable de la mauvaise utilisation du plugin WPCB mis à votre disposition gratuitement et toujours en phase d'amélioration. Vous l'utilisez en tout conscience et vous vous assurez de la protection de vos pages internet.== Screenshots ==1. Réglages du module2. Réglages ATOS3. Réglages Chèque ou Virement
4. Réglages Paypal
5. Réglages Systempay Cyberplus (Banque Populaire)
6. Réglages Mailchimp
7. Placer le shortcode wpcb sur une page / Options de paiement8. Livraison Poste française (Colis, chronopost, et d'autres mode de livraison à venir)
== Changelog ==

= 2.1 =
* Pour les détenteurs de l'API : Ajout du module de calcul de frais de port. Mon autre plugin : http://wordpress.org/extend/plugins/wp-e-commerce-livraison-france/ va être remplacé par celui çi.

= 2 =* Version beta !!!* Ajout d'une fonction sandbox pour tester vos paiement et le bon fonctionnement de votre fichier automatique response* Ajout du mode de paiement par chèque !* Ajout du mode de paiement par virement bancaire !
* Changement d'interface de réglage
* Ajout de Cyberplus Systempay= 1.1.9 =* Ajout de la session id en get dans les normal et cancel return* Ajout de automatic_response_url comme choix libre par le commercant dans le cas ou son site bloque certains dossier (deplacement manuel dans ce cas)* Correction d'une erreur avec l'affichage des milliers et decimaux dans le calcul des prix (>1000€!)* Correction mineures à droite à gauche pour plus de simplicité...= 1.1.8.1 =* Bug si Zend non installé, corrigé
= 1.1.8 =
* Mise à jour
= 1.1.5 =* Correction d'un bug de vidage de panier* Réorganisation des options* Ajout de la clé API= 1.1.3 =* Amélioration de la sécurité importante (Merci à Cyril Lecomte).= 1.1.2 =* Correction d'une erreur de suppression du plugin.=1.1.1=* Le mode test a été amélioré.= 1.1 =* Internationalized* Atos currency_code added to the settings of the plugin* Atos language added to the settings of the plugin* Atos merchant_country added to the settings of the plugin* Atos header_flag added to the settings of the plugin= 1.0.4 =* Update to wpec api v2.0= 1.0.3 =* Syntax correction= 1.0.2 =* Mises à jour mineures= 1.0.1 =* Mises à jour mineures= 1.0 =* Première version

== Upgrade Notice ==Merci de noter vos paramètres car ils peuvent êtres effacer à la mise à jour !!!!