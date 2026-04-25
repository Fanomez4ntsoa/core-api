. Appliquer : Interface + ServiceProvider (plus tard)
. Termine la Phase 1 CRM, connecte le frontend, et à ce moment-là introduis les tests automatisés — car c'est là que ça devient vraiment douloureux de tester manuellement à chaque module.

`` 
Ce que les seniors font en plus — par ordre de priorité pour toi :
1. Tests automatisés (le plus important à terme)
Au lieu de tester manuellement dans Insomnia à chaque fois, écrire des tests PHPUnit/Pest qui tournent automatiquement. Claude Code peut les écrire. Avantage : un php artisan test remplace toute ta checklist Insomnia.
2. CI/CD pipeline
Sur Railway ou GitHub Actions — à chaque push sur main, les tests tournent automatiquement et le déploiement se fait tout seul si tout passe. Tu n'as plus à déployer manuellement.
3. .env.example à jour
Petit détail mais important — s'assurer que chaque nouvelle variable .env est documentée dans .env.example pour qu'un nouveau dev puisse installer le projet sans chercher.
4. Conventional Commits plus stricts
Tu utilises déjà [FEAT]: — les seniors vont plus loin avec des scopes : feat(crm): add invoice mark-paid endpoint. Ça génère des changelogs automatiques.
``


- table users abracadabativ2 > role de shop_enabled ?

*********************************
Pour ajouter un nouvel artisan : il suffit de créer un compte professionnel sur le Core et créer sa boutique — il apparaît automatiquement sur /artisans