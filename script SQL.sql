CREATE DATABASE  IF NOT EXISTS `%1`;

DROP TABLE IF EXISTS `g_mode_reglement`;
	CREATE TABLE `g_mode_reglement` (
	`ID_MODE_REGLEMENT` int(11) NOT NULL AUTO_INCREMENT,
	`LibModeReglement` varchar(50) NOT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`Detail` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`ID_MODE_REGLEMENT`),
	KEY `WDIDX_ModeReglement_LibModeReglement` (`LibModeReglement`)
	);

	DROP TABLE IF EXISTS `taux_tva`;
	CREATE TABLE `taux_tva` (
	`ID_TAUX_TVA` int(11) NOT NULL AUTO_INCREMENT,
	`taux` decimal(5,3) NOT NULL DEFAULT '0.000',
	`Detail` varchar(255) DEFAULT NULL,
	`ACTIF` tinyint(1) DEFAULT '1',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_TAUX_TVA`),
	UNIQUE KEY `taux` (`taux`)
	);

	DROP TABLE IF EXISTS `g_mode_livraison`;
	CREATE TABLE `g_mode_livraison` (
	`ID_MODE_LIVRAISON` int(11) NOT NULL AUTO_INCREMENT,
	`LibModeLivraison` varchar(50) NOT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`Detail` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`ID_MODE_LIVRAISON`),
	KEY `WDIDX_ModeLivraison_LibModeLivraison` (`LibModeLivraison`)
	);
	
	DROP TABLE IF EXISTS `g_type_client`;
	CREATE TABLE `g_type_client` (
	`ID_TYPE_CLIENT` int(11) NOT NULL AUTO_INCREMENT,
	`Libelle` varchar(50) DEFAULT NULL,
	`Detail` varchar(255) NOT NULL,
	`ExemptTVA` tinyint(4) NOT NULL DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`VIP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_TYPE_CLIENT`)
	);
	
	DROP TABLE IF EXISTS `g_type_fourn`;
	CREATE TABLE `g_type_fourn` (
	`ID_TYPE_FOURN` int(11) NOT NULL AUTO_INCREMENT,
	`Libelle` varchar(50) DEFAULT NULL,
	`Detail` varchar(255) NOT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`VIP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_TYPE_FOURN`)
	);
	
	DROP TABLE IF EXISTS `g_etat_commande`;
	CREATE TABLE `g_etat_commande` (
	`ID_ETAT_COMMANDE` int(11) NOT NULL AUTO_INCREMENT,
	`ETAT_ORDRE` int(11) DEFAULT 0,
	`ETAT_CODE` varchar(50) NOT NULL,
	`LibEtatCommande` varchar(50) NOT NULL,
	`Detail` varchar(255) DEFAULT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`Couleur` int(11) DEFAULT '0',
	PRIMARY KEY (`ID_ETAT_COMMANDE`),
	KEY `WDIDX_EatatCommande_LibEtatCommande` (`LibEtatCommande`)
	) ;
	
	DROP TABLE IF EXISTS `g_etat_facture`;
	CREATE TABLE `g_etat_facture` (
	`ID_ETAT_FACTURE` int(11) NOT NULL AUTO_INCREMENT,
	`ETAT_ORDRE` int(11) DEFAULT 0,
	`ETAT_CODE` varchar(50) NOT NULL,
	`LibEtatFacture` varchar(50) NOT NULL,
	`Couleur` int(11) DEFAULT '0',
	`Detail` varchar(255) DEFAULT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_ETAT_FACTURE`),
	KEY `WDIDX_EatatFacture_LibEtatFacture` (`LibEtatFacture`)
	) ;
	
	DROP TABLE IF EXISTS `fournisseur`;
	CREATE TABLE `fournisseur` (
	`ID_FOURNISSEUR` int(11) NOT NULL AUTO_INCREMENT,
	`SOCIETE` varchar(255) NOT NULL,
	`PERS_PHY` tinyint(1) DEFAULT '0',
	`Nom` varchar(50) NOT NULL,
	`Prenom` varchar(50) NOT NULL,
	`Civilite` varchar(5) NOT NULL,
	`Adresse` varchar(150) NOT NULL,
	`Pays` varchar(40) NOT NULL,
	`Telephone` varchar(20) DEFAULT NULL,
	`Mobile` varchar(20) DEFAULT NULL,
	`Fax` varchar(20) DEFAULT NULL,
	`Email` varchar(40) DEFAULT NULL,
	`Ville` varchar(40) NOT NULL,
	`CodePostal` varchar(20) DEFAULT NULL,
	`IMAGE_FOURN` longblob,
	`Observations` longtext,
	`DEFAUT` tinyint(1) DEFAULT '0',
	`ID_TYPE_FOURN` int(11) NOT NULL DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_FOURNISSEUR`),
	KEY `WDIDX_Fournisseur_NomFournisseur` (`SOCIETE`)
	) ;

	DROP TABLE IF EXISTS `g_fourn_files`;
	
	CREATE TABLE `g_fourn_files` (
	`ID_FOURN_Files` int(11) NOT NULL AUTO_INCREMENT,
	`NOM_FILE` varchar(255) NOT NULL,
	`IMG_FILE` longblob,
	`ID_FOURNISSEUR` int(11) NOT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_FOURN_Files`),
	CONSTRAINT `g_FOURN_Files_ibfk_1` FOREIGN KEY (`ID_FOURNISSEUR`) REFERENCES `fournisseur` (`ID_FOURNISSEUR`)
	);
	
	DROP TABLE IF EXISTS `g_client`;
	CREATE TABLE `g_client` (
	`ID_CLIENT` int(11) NOT NULL AUTO_INCREMENT,
	`SOCIETE` varchar(255) NOT NULL,
	`PERS_PHY` tinyint(1) DEFAULT '0',
	`IMAGE_CLI` longblob,
	`Nom` varchar(50) DEFAULT NULL,
	`Prenom` varchar(50) DEFAULT NULL,
	`Civilite` varchar(5) DEFAULT NULL,
	`Adresse` varchar(150) NOT NULL,
	`ICE` varchar(100) NULL,
	`Pays` varchar(40) NOT NULL,
	`Telephone` varchar(20) DEFAULT NULL,
	`Mobile` varchar(20) DEFAULT NULL,
	`Fax` varchar(20) DEFAULT NULL,
	`Email` varchar(40) DEFAULT NULL,
	`Ville` varchar(40) NOT NULL,
	`CodePostal` varchar(20) DEFAULT NULL,
	`Observations` longtext,
	`ExemptTVA` tinyint(4) NOT NULL DEFAULT '0',
	`LivreMemeAdresse` tinyint(4) NOT NULL DEFAULT '0',
	`FactureMemeAdresse` tinyint(4) NOT NULL DEFAULT '0',
	`ID_TYPE_CLIENT` int(11) NOT NULL DEFAULT '1',
	`DEFAUT` tinyint(1) DEFAULT '0',
	`Montant_Rest_Du` decimal(24,2) NOT NULL DEFAULT '0.00',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_CLIENT`),
	KEY `WDIDX_Client_NomClient` (`SOCIETE`)
	) ;

	DROP TABLE IF EXISTS `g_client_files`;
	
	CREATE TABLE `g_client_files` (
	`ID_Client_Files` int(11) NOT NULL AUTO_INCREMENT,
	`NOM_FILE` varchar(255) NOT NULL,
	`IMG_FILE` longblob,
	`ID_CLIENT` int(11) NOT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_Client_Files`),
	CONSTRAINT `g_Client_Files_ibfk_1` FOREIGN KEY (`ID_CLIENT`) REFERENCES `g_client` (`ID_CLIENT`)
	);
	
	DROP TABLE IF EXISTS `g_adr_livraison`;
	
	CREATE TABLE `g_adr_livraison` (
	`ID_ADR_LIVRAISON` int(11) NOT NULL AUTO_INCREMENT,
	`Adresse` varchar(150) NOT NULL,
	`Pays` varchar(40) NOT NULL,
	`Ville` varchar(40) NOT NULL,
	`Telephone` varchar(20) NOT NULL,
	`Mobile` varchar(20) NOT NULL,
	`Civilite` varchar(5) NOT NULL,
	`Contact` varchar(40) NOT NULL,
	`CodePostal` varchar(20) NOT NULL,
	`Observations` longtext NOT NULL,
	`Fax` varchar(20) NOT NULL,
	`Email` varchar(40) NOT NULL,
	`ID_CLIENT` int(11) NOT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_ADR_LIVRAISON`),
	KEY `ID_CLIENT` (`ID_CLIENT`),
	CONSTRAINT `g_adr_livraison_ibfk_1` FOREIGN KEY (`ID_CLIENT`) REFERENCES `g_client` (`ID_CLIENT`)
	) ;

	
	DROP TABLE IF EXISTS `g_adr_facturation`;
	CREATE TABLE `g_adr_facturation` (
	`ID_ADR_FACTURATION` int(11) NOT NULL AUTO_INCREMENT,
	`Adresse` varchar(150) NOT NULL,
	`Pays` varchar(40) NOT NULL,
	`Ville` varchar(40) NOT NULL,
	`Telephone` varchar(20) NOT NULL,
	`Mobile` varchar(20) NOT NULL,
	`Civilite` varchar(5) NOT NULL,
	`Contact` varchar(40) NOT NULL,
	`CodePostal` varchar(20) NOT NULL,
	`Observations` longtext NOT NULL,
	`Fax` varchar(20) NOT NULL,
	`Email` varchar(40) NOT NULL,
	`ID_CLIENT` int(11) NOT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_ADR_FACTURATION`),
	KEY `ID_CLIENT` (`ID_CLIENT`),
	CONSTRAINT `g_adr_facturation_ibfk_1` FOREIGN KEY (`ID_CLIENT`) REFERENCES `g_client` (`ID_CLIENT`)
	) ;
	
	DROP TABLE IF EXISTS `famille_produit`;
	CREATE TABLE `famille_produit` (
	`ID_FAMILLE_PRODUIT` int(11) NOT NULL AUTO_INCREMENT,
	`Code_FAMILLE_PRODUIT` varchar(50) NOT NULL,
	`Lib_FAMILLE_PRODUIT` varchar(150) NOT NULL,
	`Detail_FAMILLE_PRODUIT` varchar(255) DEFAULT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_FAMILLE_PRODUIT`),
	KEY `WDIDX_FamillrPRoduitCODE_FAMILLE_PRODUIT` (`Code_FAMILLE_PRODUIT`),
	KEY `WDIDX_FamillrPRoduitLIB_FAMILLE_PRODUIT` (`Lib_FAMILLE_PRODUIT`)
	) ;
	
	DROP TABLE IF EXISTS `produit`;
	CREATE TABLE `produit` (
	`ID_PRODUIT` int(11) NOT NULL AUTO_INCREMENT,
	`IMAGE_PRODUIT` longblob NULL,
	`ID_FAMILLE_PRODUIT` int(11) NOT NULL DEFAULT '0',
	`ID_FOURNISSEUR` int(11) NOT NULL DEFAULT '0',
	`CodeBarre` varchar(50)  NULL,
	`REFERENCE` varchar(50) NOT NULL,
	`REFERENCE_FRN` varchar(50) NULL,
	`DESIGNATION` varchar(255) NOT NULL,
	`Detail` longtext NOT NULL,
	`Seuil_Alerte` int(11) NOT NULL DEFAULT '0',
	`UNITE` varchar(50) NULL,
	`PrixHT_ACHAT` decimal(24,4) NOT NULL DEFAULT '0.0000',
	`TauxTVA` decimal(5,3) NOT NULL DEFAULT '0.000',
	`PrixTTC_ACHAT` decimal(24,2) NOT NULL DEFAULT '0.00',
	`Prix_Revient` decimal(24,2) NOT NULL DEFAULT '0.00',
	`Desc_Revient` longtext,
	`PrixHT_VENTE` decimal(24,4) NOT NULL DEFAULT '0.0000',
	`PrixTTC_VENTE` decimal(24,2) NOT NULL DEFAULT '0.00',
	`NBRE_STOCK` int(11) NOT NULL DEFAULT '0',
	`Init_Stock` int(11) NOT NULL DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`is_service` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_PRODUIT`),
	KEY `WDIDX_Produit_Reference` (`REFERENCE`),
	KEY `WDIDX_Produit_Designation` (`DESIGNATION`)
	) ;
	
	
	DROP TABLE IF EXISTS `devis`;
	CREATE TABLE `devis` (
	`ID_DEVIS` int(11) NOT NULL AUTO_INCREMENT,
	`DateDevis` date NOT NULL,
	`TotalHT` decimal(24,2) NOT NULL,
	`TotalTTC` decimal(24,2) NOT NULL,
	`TotalTVA` decimal(24,2) NOT NULL,
	`Observations` longtext NOT NULL,
	`Numero_Devis` varchar(150) DEFAULT NULL,
	`Numero_Bon_Commande` varchar(150) DEFAULT NULL,
	`ID_CLIENT` int(11) NOT NULL,
	`DV_LIVREE` tinyint(1) DEFAULT '0',
	`EDIT` tinyint(1) DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_DEVIS`),
	KEY `WDIDX_Devis_NumClient` (`ID_CLIENT`),
	KEY `WDIDX_Devis_NumDevis` (`Numero_Devis`),
	CONSTRAINT `devis_ibfk_1` FOREIGN KEY (`ID_CLIENT`) REFERENCES `g_client` (`ID_CLIENT`)
	);
	
	
	DROP TABLE IF EXISTS `lignedevis`;
	CREATE TABLE `lignedevis` (
	`ID_LigneDevis` int(11) NOT NULL AUTO_INCREMENT,
	`ID_PRODUIT` int(11) NOT NULL,
	`ID_DEVIS` int(11) NOT NULL,
	`Quantite` decimal(24,2) NOT NULL DEFAULT '0.00',
	`TauxTVA` decimal(5,3) NOT NULL DEFAULT '0.000',
	`PrixU` decimal(24,4) NOT NULL DEFAULT '0.0000',
	`PrixHT` decimal(24,2) NOT NULL DEFAULT '0.00',
	`Remise` decimal(24,2) NOT NULL DEFAULT '0.00',
	`PrixTTC` decimal(24,2) NOT NULL DEFAULT '0.00',
	`ORDRE` smallint(6) NOT NULL DEFAULT '0',
	`is_produit_fini` tinyint(1) DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_LigneDevis`),
	KEY `WDIDX_LigneDevis_ID_DEVIS` (`ID_DEVIS`),
	KEY `ID_PRODUIT` (`ID_PRODUIT`),
	CONSTRAINT `lignedevis_ibfk_2` FOREIGN KEY (`ID_DEVIS`) REFERENCES `devis` (`ID_DEVIS`),
	CONSTRAINT `lignedevis_ibfk_1` FOREIGN KEY (`ID_PRODUIT`) REFERENCES `produit` (`ID_PRODUIT`)
	);

	
	DROP TABLE IF EXISTS `commande`;
	CREATE TABLE `commande` (
	`ID_COMMANDE` int(11) NOT NULL AUTO_INCREMENT,
	`DateCommande` date NOT NULL,
	`TotalHT` decimal(24,2) NOT NULL,
	`TotalTTC` decimal(24,2) NOT NULL,
	`TotalTVA` decimal(24,2) NOT NULL,
	`Observations` longtext NOT NULL,
	`Numero_Commande` varchar(150) DEFAULT NULL,
	`ID_FOURNISSEUR` int(11) NOT NULL,
	`EDIT` tinyint(1) DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`ID_ETAT_COMMANDE` int(11) NOT NULL,
	`CMD_LIVREE` tinyint(4) DEFAULT '0',
	PRIMARY KEY (`ID_COMMANDE`),
	KEY `ID_ETAT_COMMANDE` (`ID_ETAT_COMMANDE`),
	KEY `WDIDX_Commande_NumClient` (`ID_FOURNISSEUR`),
	KEY `WDIDX_Commande_FOURNISSEUR` (`ID_FOURNISSEUR`),
	CONSTRAINT `commande_ibfk_6` FOREIGN KEY (`ID_ETAT_COMMANDE`) REFERENCES `g_etat_commande` (`ID_ETAT_COMMANDE`),
	CONSTRAINT `commande_ibfk_7` FOREIGN KEY (`ID_FOURNISSEUR`) REFERENCES `fournisseur` (`ID_FOURNISSEUR`)
	);
	
	DROP TABLE IF EXISTS `lignecommande`;
	CREATE TABLE `lignecommande` (
	`ID_LigneCommande` int(11) NOT NULL AUTO_INCREMENT,
	`ID_PRODUIT` int(11) NOT NULL,
	`ID_COMMANDE` int(11) NOT NULL,
	`Quantite` decimal(24,2) NOT NULL DEFAULT '0.00',
	`TauxTVA` decimal(5,3) NOT NULL DEFAULT '0.000',
	`PrixU` decimal(24,4) NOT NULL DEFAULT '0.0000',
	`PrixHT` decimal(24,2) NOT NULL DEFAULT '0.00',
	`Remise` decimal(24,2) NOT NULL DEFAULT '0.00',
	`PrixTTC` decimal(24,2) NOT NULL DEFAULT '0.00',
	`ORDRE` smallint(6) NOT NULL DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_LigneCommande`),
	KEY `WDIDX_LigneCommande_ID_COMMANDE` (`ID_COMMANDE`),
	KEY `ID_PRODUIT` (`ID_PRODUIT`),
	CONSTRAINT `lignecommande_ibfk_2` FOREIGN KEY (`ID_COMMANDE`) REFERENCES `commande` (`ID_COMMANDE`),
	CONSTRAINT `lignecommande_ibfk_1` FOREIGN KEY (`ID_PRODUIT`) REFERENCES `produit` (`ID_PRODUIT`)
	);

	DROP TABLE IF EXISTS `facture`;
	
	CREATE TABLE `facture` (
	`ID_FACTURE` int(11) NOT NULL AUTO_INCREMENT,
	`DateFacture` date DEFAULT NULL,
	`Numero_Facture` varchar(150) DEFAULT NULL,
	`TotalHT` decimal(24,2) NOT NULL,
	`TotalTTC` decimal(24,2) NOT NULL,
	`TotalTVA` decimal(24,2) NOT NULL,
	`ID_ETAT_FACTURE` int(11) DEFAULT NULL,
	`ID_COMMANDE` int(11) DEFAULT '0',
	`ID_DEVIS` int(11) DEFAULT '0',
	`ID_CLIENT` int(11) DEFAULT '0',
	`EDIT` tinyint(1) DEFAULT '0',
	`ID_CONDITION_REGL` int(11) DEFAULT 0,
	`DateEcheance` date DEFAULT NULL,
	`DateReglement` date DEFAULT NULL,
	`REGLEE` tinyint(1) DEFAULT '0',
	`Montant_REGLEMENT` decimal(24,2) NOT NULL DEFAULT '0.00',
	`ID_MODE_REGLEMENT` int(11) DEFAULT 0,
	`His_Reglement` longtext,
	`Observations` longtext,
	`numero_cheque` varchar(100) DEFAULT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_FACTURE`)
	);

	DROP TABLE IF EXISTS `stock`;
	
	CREATE TABLE `stock` (
	`ID_STOCK` int(11) NOT NULL AUTO_INCREMENT,
	`ID_PRODUIT` int(11) NOT NULL,
	`Quantite` decimal(24,2) NOT NULL DEFAULT '0.00',
	`DernirMVM` int(11) NOT NULL DEFAULT '0',
	`Notes` longtext NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_STOCK`),
	KEY `WDIDX_STOCK_ID_PRODUIT` (`ID_PRODUIT`),
	CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`ID_PRODUIT`) REFERENCES `produit` (`ID_PRODUIT`)
	);
	
	DROP TABLE IF EXISTS `bonreception`;
	
	CREATE TABLE `bonreception` (
	`ID_BonReception` int(11) NOT NULL AUTO_INCREMENT,
	`DateBonReception` date DEFAULT NULL,
	`Numero_BonReception` varchar(150) DEFAULT NULL,
	`ID_COMMANDE` int(11) NOT NULL,
	`EDIT` tinyint(1) DEFAULT '0',
	`Observations` longtext,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_BonReception`)
	);
	
	DROP TABLE IF EXISTS `bonlivraison`;
	CREATE TABLE `bonlivraison` (
	`ID_BonLivraison` int(11) NOT NULL AUTO_INCREMENT,
	`DateBonLivraison` date DEFAULT NULL,
	`Numero_BonLivraison` varchar(150) DEFAULT NULL,
	`ID_DEVIS` int(11) NOT NULL,
	`EDIT` tinyint(1) DEFAULT '0',
	`Observations` longtext,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_BonLivraison`),
	CONSTRAINT `gc_devis_ibfk` FOREIGN KEY (`ID_DEVIS`) REFERENCES `devis` (`ID_DEVIS`)
	);
	
	DROP TABLE IF EXISTS `mvm_stock`;
	
	CREATE TABLE `mvm_stock` (
	`ID_MVM_STOCK` int(11) NOT NULL AUTO_INCREMENT,
	`ID_STOCK` int(11) NOT NULL,
	`Quantite` decimal(24,2) NOT NULL DEFAULT '0.00',
	`LIB_MVM` varchar(100) DEFAULT NULL,
	`MODE` int(4) NOT NULL DEFAULT '0',
	`ID_COMMANDE` int(11) DEFAULT '0',
	`ID_DEVIS` int(11) DEFAULT '0',
	`ID_FACTURE_AVOIR` int(11) DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_MVM_STOCK`),
	KEY `WDIDX_MVM_STOCK_ID_PRODUIT` (`ID_MVM_STOCK`),
	CONSTRAINT `MVM_stock_ibfk_1` FOREIGN KEY (`ID_STOCK`) REFERENCES `stock` (`ID_STOCK`)
	) ;
	
	DROP TABLE IF EXISTS `etat_param`;
	
	CREATE TABLE `etat_param` (
	`ID_ETAT_PARAM` int(11) NOT NULL AUTO_INCREMENT,
	`ENTETE` longtext DEFAULT NULL,
	`BAS_PAGE` longtext DEFAULT NULL,
	`FLAG_IMG_ETAT` tinyint(1) DEFAULT '0',
	`IMG_ETAT` longblob,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_ETAT_PARAM`),
	KEY `WDIDX_ETAT_PARAM` (`ID_ETAT_PARAM`)
	);
	
	-- Table structure for table `his_produit`
	
	DROP TABLE IF EXISTS `his_produit`;
	
	CREATE TABLE `his_produit` (
	`ID_HIS_PRODUIT` int(11) NOT NULL AUTO_INCREMENT,
	`ID_PRODUIT` int(11) NOT NULL,
	`ID_FAMILLE_PRODUIT` int(11) NOT NULL DEFAULT '0',
	`ID_FOURNISSEUR` int(11) NOT NULL DEFAULT '0',
	`CodeBarre` varchar(50) NOT NULL,
	`REFERENCE` varchar(50) NOT NULL,
	`REFERENCE_FRN` varchar(50) NULL,
	`DESIGNATION` varchar(255) NOT NULL,
	`Detail` longtext NOT NULL,
	`Seuil_Alerte` int(11) NOT NULL DEFAULT '0',
	`PrixHT_ACHAT` decimal(24,4) NOT NULL DEFAULT '0.0000',
	`TauxTVA` decimal(5,3) NOT NULL DEFAULT '0.000',
	`PrixTTC_ACHAT` decimal(24,2) NOT NULL DEFAULT '0.00',
	`Prix_Revient` decimal(24,2) NOT NULL DEFAULT '0.00',
	`Desc_Revient` longtext,
	`PrixHT_VENTE` decimal(24,4) NOT NULL DEFAULT '0.0000',
	`PrixTTC_VENTE` decimal(24,2) NOT NULL DEFAULT '0.00',
	`NBRE_STOCK` int(11) NOT NULL DEFAULT '0',
	`Action_PRD` varchar(50) NOT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`is_service` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_HIS_PRODUIT`),
	KEY `WDIDX_Produit_Reference` (`REFERENCE`),
	KEY `WDIDX_Produit_Designation` (`DESIGNATION`),
	KEY `ID_PRODUIT` (`ID_PRODUIT`),
	CONSTRAINT `produit_fk_1` FOREIGN KEY (`ID_PRODUIT`) REFERENCES `produit` (`ID_PRODUIT`)
	);
	
	-- Condition de réglement
	CREATE TABLE `g_condition_reglement` (
	`ID_CONDITION_REGL` int(11) NOT NULL AUTO_INCREMENT,
	`Libelle_CONDITION_REGL` varchar(50) NOT NULL,
	`Detail` varchar(255) DEFAULT NULL,
	`NBRE_JOUR` int(11) DEFAULT 0,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_CONDITION_REGL`),
	KEY `WDIDX_CONDITION_REGL_Libelle_CONDITION_REGL` (`Libelle_CONDITION_REGL`)
	);
	
	-- Table facture_avoir
	DROP TABLE IF EXISTS `facture_avoir`;
	
	CREATE TABLE `facture_avoir` (
	`ID_FACTURE_AVOIR` int(11) NOT NULL AUTO_INCREMENT,
	`DateFacture` date DEFAULT NULL,
	`Numero_Facture` varchar(150) DEFAULT NULL,
	`TotalHT` decimal(24,2) NOT NULL,
	`TotalTTC` decimal(24,2) NOT NULL,
	`TotalTVA` decimal(24,2) NOT NULL,
	`ID_ETAT_FACTURE` int(11) DEFAULT NULL,
	`ID_CLIENT` int(11) DEFAULT NULL,
	`ID_FACTURE` int(11) NULL,
	`EDIT` tinyint(1) DEFAULT '0',
	`ID_CONDITION_REGL` int(11) DEFAULT 0,
	`DateEcheance` date DEFAULT NULL,
	`DateReglement` date DEFAULT NULL,
	`Montant_REGLEMENT` decimal(24,2) NOT NULL DEFAULT '0.00',
	`ID_MODE_REGLEMENT` int(11) DEFAULT 0,
	`REGLEE` tinyint(1) DEFAULT '0',
	`Observations` longtext,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_FACTURE_AVOIR`)
	);
	
	-- Table ligne_facture_avoir
	
	DROP TABLE IF EXISTS `ligne_facture_avoir`;
	
	CREATE TABLE `ligne_facture_avoir` (
	`ID_LigneFactureAvoir` int(11) NOT NULL AUTO_INCREMENT,
	`ID_PRODUIT` int(11) NOT NULL,
	`LIBELLE` varchar(255) DEFAULT NULL,
	`ID_FACTURE_AVOIR` int(11) NOT NULL,
	`PrixU` decimal(24,4) NOT NULL DEFAULT '0.0000',
	`TauxTVA` decimal(5,3) NOT NULL DEFAULT '0.000',
	`PrixHT` decimal(24,2) NOT NULL DEFAULT '0.00',
	`Quantite` decimal(24,2) NOT NULL DEFAULT '0.00',
	`Remise` decimal(24,2) NOT NULL DEFAULT '0.00',
	`PrixTTC` decimal(24,2) NOT NULL DEFAULT '0.00',
	`ORDRE` smallint(6) NOT NULL DEFAULT '0',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	`is_produit_fini` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_LigneFactureAvoir`),
	CONSTRAINT `ligne_facture_avoir_ibfk_2` FOREIGN KEY (`ID_FACTURE_AVOIR`) REFERENCES `facture_avoir` (`ID_FACTURE_AVOIR`)
	);
	
	DROP TABLE IF EXISTS `devise`;
	CREATE TABLE `devise` (
	`ID_DEVISE` int(11) NOT NULL AUTO_INCREMENT,
	`Devise` varchar(100) DEFAULT NULL,
	`ACTIF` tinyint(1) DEFAULT '1',
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_DEVISE`)
	);
	
	DROP TABLE IF EXISTS `depense_Recurrente`;
	CREATE TABLE `depense_Recurrente` (
	`ID_DEPENSE_REC` int(11) NOT NULL AUTO_INCREMENT,
	`Libelle` varchar(255) DEFAULT NULL,
	`Note` longtext DEFAULT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_DEPENSE_REC`)
	);
	
	DROP TABLE IF EXISTS `depense`;
	CREATE TABLE `depense` (
	`ID_DEPENSE` int(11) NOT NULL AUTO_INCREMENT,
	`Num_Depense` varchar(100) DEFAULT NULL,
	`Description` varchar(255) DEFAULT NULL,
	`Note` longtext DEFAULT NULL,
	`ID_CATG` int(11) NOT NULL,
	`PayeA` varchar(100) DEFAULT NULL,
	`DateDep` date DEFAULT NULL,
	`ID_STATUT` int(11) NOT NULL,
	`Total` decimal(24,2) NOT NULL DEFAULT '0.000',
	`NoteReg` longtext DEFAULT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_DEPENSE`),
	KEY `WDIDX_Description` (`Description`)
	);
	
	DROP TABLE IF EXISTS `g_etat_depense`;
	CREATE TABLE `g_etat_depense` (
	`ID_ETAT_DEPENSE` int(11) NOT NULL AUTO_INCREMENT,
	`ETAT_ORDRE` int(11) DEFAULT 0,
	`ETAT_CODE` varchar(50) NOT NULL,
	`LibEtatDepense` varchar(100) NOT NULL,
	`Couleur` int(11) DEFAULT '0',
	`Detail` varchar(255) DEFAULT NULL,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_ETAT_DEPENSE`),
	KEY `WDIDX_EtatDepense_LibEtat` (`LibEtatDepense`)
	) ;
	
	DROP TABLE IF EXISTS `g_cat_depense`;
	CREATE TABLE `g_cat_depense` (
	`ID_CAT_DEPENSE` int(11) NOT NULL AUTO_INCREMENT,
	`LIBELLE` varchar(255) DEFAULT NULL,
	`DETAIL` varchar(255) DEFAULT NULL,
	`ID_PARENT` int(11) DEFAULT 0,
	`PFL_CRE` varchar(50) DEFAULT NULL,
	`DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`PFL_MAJ` varchar(50) DEFAULT NULL,
	`DAT_MAJ` timestamp,
	`PFL_SUP` varchar(50) DEFAULT NULL,
	`DAT_SUP` timestamp,
	`SUPP` tinyint(1) DEFAULT '0',
	PRIMARY KEY (`ID_CAT_DEPENSE`)
	);
	
	-- Table structure for table his_reglement
	
	DROP TABLE IF EXISTS his_reglement;
	
	CREATE TABLE his_reglement (
	id_his_reglement int(11) NOT NULL AUTO_INCREMENT,
	id_facture int(11) NOT NULL,
	date_reglement date DEFAULT NULL,
	montant_reglement decimal(24,2) NOT NULL DEFAULT '0.00',
	id_mode_reglement int(11) DEFAULT '0',
	Observations longtext,
	PFL_CRE varchar(50) DEFAULT NULL,
	DAT_CRE timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PFL_MAJ varchar(50) DEFAULT NULL,
	DAT_MAJ timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	PFL_SUP varchar(50) DEFAULT NULL,
	DAT_SUP timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	SUPP tinyint(1) DEFAULT '0',
	PRIMARY KEY (id_his_reglement)
	);
	
-- Table produit fini
CREATE TABLE `produit_fini` (
  `ID_PRODUIT_FINI` int(11) NOT NULL AUTO_INCREMENT,
  `IMAGE_PRODUIT` longblob,
  `ID_FAMILLE_PRODUIT` int(11) NOT NULL DEFAULT '0',
  `REFERENCE` varchar(50) NOT NULL,
  `DESIGNATION` varchar(100) NOT NULL,
  `DETAIL` longtext NOT NULL,
  `TauxTVA` decimal(5,3) NOT NULL DEFAULT '0.000',
  `PrixTVA` decimal(24,2) NOT NULL DEFAULT '0.00',
  `PrixHT` decimal(24,2) NOT NULL DEFAULT '0.00',
  `PrixTTC` decimal(24,2) NOT NULL DEFAULT '0.00',
  `PFL_CRE` varchar(50) DEFAULT NULL,
  `DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `PFL_MAJ` varchar(50) DEFAULT NULL,
  `DAT_MAJ` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PFL_SUP` varchar(50) DEFAULT NULL,
  `DAT_SUP` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `SUPP` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`ID_PRODUIT_FINI`),
  KEY `WDIDX_Produit_fini_Reference` (`REFERENCE`),
  KEY `WDIDX_Produit_fini_Designation` (`DESIGNATION`)
);

-- Table corr_produiy_fini_produit
CREATE TABLE `corr_produit_fini_produit` (
  `ID_PRODUIT_FINI_PRODUIT` int(11) NOT NULL AUTO_INCREMENT,
  `ID_PRODUIT_FINI` int(11) NOT NULL,
  `ID_PRODUIT` int(11) NOT NULL,
  `QUANTITE` int(11) NOT NULL DEFAULT '1',
  `MontantTVA` decimal(24,2) NOT NULL DEFAULT '0.00',
  `MontantHT` decimal(24,2) NOT NULL DEFAULT '0.00',
  `MontantTTC` decimal(24,2) NOT NULL DEFAULT '0.00',
  `PFL_CRE` varchar(50) DEFAULT NULL,
  `DAT_CRE` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `PFL_MAJ` varchar(50) DEFAULT NULL,
  `DAT_MAJ` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PFL_SUP` varchar(50) DEFAULT NULL,
  `DAT_SUP` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `SUPP` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`ID_PRODUIT_FINI_PRODUIT`)
);

	-- Initialiser la devise
	INSERT INTO `devise` VALUES (1,'DH',1,'ADMIN',CURRENT_TIMESTAMP,'ADMIN',CURRENT_TIMESTAMP,NULL,'0000-00-00 00:00:00',0);
	
	-- Insertion des états de facture
	Insert into g_etat_facture(ETAT_ORDRE,ETAT_CODE,LibEtatFacture,Detail,PFL_CRE,Couleur) VALUES(1,'BRL','Brouillon','Etat Initiale de la facture lors de la créaion, n''est encore finalisée','ADMIN',13880704);
	Insert into g_etat_facture(ETAT_ORDRE,ETAT_CODE,LibEtatFacture,Detail,PFL_CRE,Couleur) VALUES(2,'OVR','Ouverte','La facture est crée et envoyée au client en attente de réglement','ADMIN',11758136);
	Insert into g_etat_facture(ETAT_ORDRE,ETAT_CODE,LibEtatFacture,Detail,PFL_CRE,Couleur) VALUES(3,'PAY','Payée','La facture a été réglée en totalité','ADMIN',39168);
	Insert into g_etat_facture(ETAT_ORDRE,ETAT_CODE,LibEtatFacture,Detail,PFL_CRE,Couleur) VALUES(4,'RTD','En retard','La facture n''a pas été réglée et que la date d''échéance est passée','ADMIN',3307753);
	Insert into g_etat_facture(ETAT_ORDRE,ETAT_CODE,LibEtatFacture,Detail,PFL_CRE,Couleur) VALUES(5,'ANL','Annulée','La facture n''est pas acceptée par le client (Annulée)','ADMIN',6446321);
	
	-- Insertion des Conditions de Réglement
	Insert into g_condition_reglement(Libelle_CONDITION_REGL,Detail,NBRE_JOUR,PFL_CRE) VALUES('Paiement immédiat','Paiment sur place sans attendre la fin de la journée',0,'ADMIN');
	Insert into g_condition_reglement(Libelle_CONDITION_REGL,Detail,NBRE_JOUR,PFL_CRE) VALUES('Aujourd''hui','Paiment au cours de la journée',1,'ADMIN');
	Insert into g_condition_reglement(Libelle_CONDITION_REGL,Detail,NBRE_JOUR,PFL_CRE) VALUES('15 jours','Paiment dans les 15 jours qui suive',15,'ADMIN');
	Insert into g_condition_reglement(Libelle_CONDITION_REGL,Detail,NBRE_JOUR,PFL_CRE) VALUES('Un mois','Paiment dans la limite d''un mois',30,'ADMIN');
	Insert into g_condition_reglement(Libelle_CONDITION_REGL,Detail,NBRE_JOUR,PFL_CRE) VALUES('Autre','Spécifier une autre période de réglement',-1,'ADMIN');
	
	-- Insertion des états de dépenses
	Insert into g_etat_depense(ETAT_ORDRE,ETAT_CODE,LibEtatDepense,Detail,PFL_CRE,Couleur) VALUES(1,'BRL','Brouillon','Etat initiale d''une dépense qui vient d''être créee','ADMIN',13880704);
	Insert into g_etat_depense(ETAT_ORDRE,ETAT_CODE,LibEtatDepense,Detail,PFL_CRE,Couleur) VALUES(2,'PAY','Payée','La dépense a été réglée en totalité','ADMIN',39168);
	Insert into g_etat_depense(ETAT_ORDRE,ETAT_CODE,LibEtatDepense,Detail,PFL_CRE,Couleur) VALUES(3,'RTD','En retard','La dépense n''a pas été réglée et que la date d''échéance est passée','ADMIN',3307753);
	Insert into g_etat_depense(ETAT_ORDRE,ETAT_CODE,LibEtatDepense,Detail,PFL_CRE,Couleur) VALUES(4,'ANL','Annulée','La facture n''est pas acceptée  (Annulée)','ADMIN',6446321);
	
	-- Insertion g_mode_reglement
	Insert into g_mode_reglement(LibModeReglement,PFL_CRE) VALUES('Espèces','ADMIN');
	Insert into g_mode_reglement(LibModeReglement,PFL_CRE) VALUES('Chèque','ADMIN');
	Insert into g_mode_reglement(LibModeReglement,PFL_CRE) VALUES('Carte bancaire','ADMIN');
	Insert into g_mode_reglement(LibModeReglement,PFL_CRE) VALUES('Virement','ADMIN');
	
	-- Mode de livraison
	
	Insert into g_mode_livraison(LibModeLivraison,PFL_CRE) VALUES('Normal','ADMIN');
	Insert into g_mode_livraison(LibModeLivraison,PFL_CRE) VALUES('Recommandé','ADMIN');
	Insert into g_mode_livraison(LibModeLivraison,PFL_CRE) VALUES('Transporteur','ADMIN');
	
	-- Etat de Commande,Devis
	
	Insert into g_etat_commande(ETAT_ORDRE,ETAT_CODE,LibEtatCommande,Detail,PFL_CRE,Couleur) VALUES(1,'ENC','En cours','La commande est crée en attendant qu''elle soit envoyée au fournisseur','ADMIN',13880704);  
	Insert into g_etat_commande(ETAT_ORDRE,ETAT_CODE,LibEtatCommande,Detail,PFL_CRE,Couleur) VALUES(2,'OVR','Ouverte','La commande est envoyée au fournisseur en attendant la livraison','ADMIN',11758136);  
	Insert into g_etat_commande(ETAT_ORDRE,ETAT_CODE,LibEtatCommande,Detail,PFL_CRE,Couleur) VALUES(3,'REC','Reçu','La commande est livrée par le fournisseur','ADMIN',39168);  
	Insert into g_etat_commande(ETAT_ORDRE,ETAT_CODE,LibEtatCommande,Detail,PFL_CRE,Couleur) VALUES(4,'ANL','Annulée','La commande est annulée par l''utilisateur ou le fournisseur','ADMIN',6446321);
	
	-- Catégories dépenses
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'charges variables'
	, 'charges variables'
	, 0
	, 'SUPADMIN'
	, SYSDATE()
	);  
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'charges fixes'
	, 'charges fixes'
	, 0
	, 'SUPADMIN'
	, SYSDATE()
	);  
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Autres'
	, 'Autres charges'
	, 0
	, 'SUPADMIN'
	, SYSDATE()
	);
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Fête & Cadeaux'
	, 'cérémonies et fêtes'
	, 1
	, 'SUPADMIN'
	, SYSDATE()
	);
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Frais de sous-traitance'
	, 'les frais de sous-traitance'
	, 1
	, 'SUPADMIN'
	, SYSDATE()	
	);
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Commissions'
	, 'commissions versées aux apporteurs d’affaires...'
	, 1
	, 'SUPADMIN'
	, SYSDATE()	
	);
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Achat de marchandises ou matières'
	, 'Achat de marchandises ou matières'
	, 1
	, 'SUPADMIN'
	, SYSDATE()	
	);
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Loyer'
	, 'Loyers de l''immeuble ou dépots ...'
	, 2
	, 'SUPADMIN'
	, SYSDATE()	
	);	
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Eau, Electricité, Abonnement'
	, 'Factures Eau, Électricité, Abonnement Fixe Mobile ...'
	, 2
	, 'SUPADMIN'
	, SYSDATE()	
	);	
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Frais de carburant'
	, 'frais de carburant transport ...'
	, 2
	, 'SUPADMIN'
	, SYSDATE()	
	);	
	INSERT INTO g_cat_depense (LIBELLE,DETAIL,ID_PARENT,PFL_CRE,DAT_CRE)
	VALUES
	( 'Frais restauration'
	, 'frais restauration et hôtels ...'
	, 2
	, 'SUPADMIN'
	, SYSDATE()	
	);

	-- Type de client
	
	Insert into g_type_client(Libelle,Detail,ExemptTVA,VIP,PFL_CRE) VALUES('Normal','Client normal sans avantages spécifiques',0,0,'ADMIN');
	Insert into g_type_client(Libelle,Detail,ExemptTVA,VIP,PFL_CRE) VALUES('VIP','Client avec plus des avantages',1,1,'ADMIN')
		

DROP TABLE IF EXISTS `g_etat_dette`;
CREATE TABLE `g_etat_dette` (
    `ID_ETAT_DETTE`     int(11)         NOT NULL AUTO_INCREMENT,
    `ETAT_ORDRE`        int(11)         DEFAULT 0,
    `ETAT_CODE`         varchar(50)     NOT NULL,
    `LibEtatDette`      varchar(100)    NOT NULL,
    `Couleur`           int(11)         DEFAULT '0',
    `Detail`            varchar(255)    DEFAULT NULL,
    `PFL_CRE`           varchar(50)     DEFAULT NULL,
    `DAT_CRE`           timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `PFL_MAJ`           varchar(50)     DEFAULT NULL,
    `DAT_MAJ`           timestamp,
    `PFL_SUP`           varchar(50)     DEFAULT NULL,
    `DAT_SUP`           timestamp,
    `SUPP`              tinyint(1)      DEFAULT '0',
    PRIMARY KEY (`ID_ETAT_DETTE`),
    KEY `WDIDX_EtatDette_Lib` (`LibEtatDette`)
) COMMENT='Référentiel des états possibles d''une dette fournisseur';

-- 2. TABLE PRINCIPALE : DETTE FOURNISSEUR

DROP TABLE IF EXISTS `dette_fournisseur`;
CREATE TABLE `dette_fournisseur` (
    `ID_DETTE`              int(11)         NOT NULL AUTO_INCREMENT,
    `Numero_Dette`          varchar(150)    DEFAULT NULL                    COMMENT 'Numéro auto-généré ex: DT-20240101-001',
    `ID_FOURNISSEUR`        int(11)         NOT NULL                        COMMENT 'Fournisseur concerné',
    `ID_COMMANDE`           int(11)         DEFAULT NULL                    COMMENT 'Commande source (optionnelle)',
    `ID_BonReception`       int(11)         DEFAULT NULL                    COMMENT 'Bon de réception source (optionnelle)',
    `DateDette`             date            NOT NULL                        COMMENT 'Date de création de la dette',
    `DateEcheance`          date            DEFAULT NULL                    COMMENT 'Date limite de règlement',
    `MontantTotal`          decimal(24,2)   NOT NULL DEFAULT '0.00'         COMMENT 'Montant total de la dette',
    `MontantRegle`          decimal(24,2)   NOT NULL DEFAULT '0.00'         COMMENT 'Montant déjà réglé (cumul)',
    `MontantRestant`        decimal(24,2)   NOT NULL DEFAULT '0.00'         COMMENT 'MontantTotal - MontantRegle',
    `ID_ETAT_DETTE`         int(11)         NOT NULL DEFAULT '1'            COMMENT 'État courant de la dette',
    `ID_MODE_REGLEMENT`     int(11)         DEFAULT NULL                    COMMENT 'Mode de règlement par défaut',
    `Observations`          longtext        DEFAULT NULL,
    `EDIT`                  tinyint(1)      DEFAULT '0'                     COMMENT '1 = finalisée / non modifiable',
    `PFL_CRE`               varchar(50)     DEFAULT NULL,
    `DAT_CRE`               timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `PFL_MAJ`               varchar(50)     DEFAULT NULL,
    `DAT_MAJ`               timestamp,
    `PFL_SUP`               varchar(50)     DEFAULT NULL,
    `DAT_SUP`               timestamp,
    `SUPP`                  tinyint(1)      DEFAULT '0',
    PRIMARY KEY (`ID_DETTE`),
    KEY `idx_dette_fournisseur`  (`ID_FOURNISSEUR`),
    KEY `idx_dette_br`           (`ID_BonReception`),
    KEY `idx_dette_etat`         (`ID_ETAT_DETTE`),
    CONSTRAINT `dette_fourn_ibfk_1` FOREIGN KEY (`ID_FOURNISSEUR`)  REFERENCES `fournisseur`    (`ID_FOURNISSEUR`),
    CONSTRAINT `dette_fourn_ibfk_2` FOREIGN KEY (`ID_ETAT_DETTE`)   REFERENCES `g_etat_dette`   (`ID_ETAT_DETTE`)
) COMMENT='Dettes envers les fournisseurs';

-- 3. HISTORIQUE DES RÈGLEMENTS D'UNE DETTE

DROP TABLE IF EXISTS `his_reglement_dette`;
CREATE TABLE `his_reglement_dette` (
    `ID_HIS_REG_DETTE`      int(11)         NOT NULL AUTO_INCREMENT,
    `ID_DETTE`              int(11)         NOT NULL,
    `DateReglement`         date            DEFAULT NULL,
    `MontantReglement`      decimal(24,2)   NOT NULL DEFAULT '0.00',
    `ID_MODE_REGLEMENT`     int(11)         DEFAULT '0',
    `Numero_Cheque`         varchar(100)    DEFAULT NULL,
    `Observations`          longtext        DEFAULT NULL,
    `PFL_CRE`               varchar(50)     DEFAULT NULL,
    `DAT_CRE`               timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `PFL_MAJ`               varchar(50)     DEFAULT NULL,
    `DAT_MAJ`               timestamp,
    `PFL_SUP`               varchar(50)     DEFAULT NULL,
    `DAT_SUP`               timestamp,
    `SUPP`                  tinyint(1)      DEFAULT '0',
    PRIMARY KEY (`ID_HIS_REG_DETTE`),
    KEY `idx_his_reg_dette` (`ID_DETTE`),
    CONSTRAINT `his_reg_dette_ibfk_1` FOREIGN KEY (`ID_DETTE`) REFERENCES `dette_fournisseur` (`ID_DETTE`)
) COMMENT='Historique des paiements effectués sur une dette fournisseur';

-- ============================================================
-- 4. DONNÉES DE RÉFÉRENCE : ÉTATS DE LA DETTE
-- ============================================================

INSERT INTO `g_etat_dette` (ETAT_ORDRE, ETAT_CODE, LibEtatDette, Detail, PFL_CRE, Couleur) VALUES
(1, 'BRL', 'Brouillon',   'Dette créée, non encore validée',                          'ADMIN', 13880704),
(2, 'OVR', 'Ouverte',     'Dette validée, en attente de règlement total',              'ADMIN', 11758136),
(3, 'PPY', 'Partielle',   'Dette partiellement réglée',                               'ADMIN', 16753920),
(4, 'PAY', 'Soldée',      'Dette intégralement réglée',                               'ADMIN', 39168),
(5, 'RTD', 'En retard',   'Date déchéance dépassée, dette non soldée',              'ADMIN', 3307753),
(6, 'ANL', 'Annulée',     'Dette annulée (erreur, avoir, litige résolu)',              'ADMIN', 6446321);

-- ============================================================
-- 5. Création de l'Entête de l'Avoir
-- ============================================================

DROP TABLE IF EXISTS `facture_avoir_fournisseur`;
CREATE TABLE `facture_avoir_fournisseur` (
    `ID_AVOIR_FOURN`    int(11)         NOT NULL AUTO_INCREMENT,
    `Numero_Avoir`      varchar(150)    DEFAULT NULL,
    `ID_FOURNISSEUR`    int(11)         NOT NULL,
    `ID_DETTE`          int(11)         NOT NULL,
    `DateAvoir`         date            NOT NULL,
    `MontantAvoir`      decimal(24,2)   NOT NULL DEFAULT '0.00',
    `Motif_Retour`      varchar(255)    DEFAULT NULL,
    `EDIT`              tinyint(1)      DEFAULT '0',
	`TotalHT` 			decimal(24,2) NOT NULL,
	`TotalTTC` 			decimal(24,2) NOT NULL,
	`TotalTVA` 			decimal(24,2) NOT NULL,
	`DateReglement` 	date DEFAULT NULL,
	`Montant_REGLEMENT` decimal(24,2) NOT NULL DEFAULT '0.00',
	`ID_MODE_REGLEMENT` int(11) DEFAULT 0,
	`ID_ETAT_FACTURE` int(11) DEFAULT NULL,
    `PFL_CRE`           varchar(50)     DEFAULT NULL,
    `DAT_CRE`           timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `PFL_MAJ`           varchar(50)     DEFAULT NULL,
    `DAT_MAJ`           timestamp       NULL DEFAULT NULL,
    `PFL_SUP`           varchar(50)     DEFAULT NULL,
    `DAT_SUP`           timestamp       NULL DEFAULT NULL,
    `SUPP`              tinyint(1)      DEFAULT '0',
    PRIMARY KEY (`ID_AVOIR_FOURN`),
    CONSTRAINT `fk_avoir_frn` FOREIGN KEY (`ID_FOURNISSEUR`) REFERENCES `fournisseur` (`ID_FOURNISSEUR`),
    CONSTRAINT `fk_avoir_dette` FOREIGN KEY (`ID_DETTE`) REFERENCES `dette_fournisseur` (`ID_DETTE`)
);

-- ============================================================
-- 6. Création des Lignes de l'Avoir (Le détail des retours)
-- ============================================================

DROP TABLE IF EXISTS `lignes_avoir_fournisseur`;
CREATE TABLE `lignes_avoir_fournisseur` (
    `ID_LIGNE_AVOIR`    int(11)         NOT NULL AUTO_INCREMENT,
    `ID_AVOIR_FOURN`    int(11)         NOT NULL,
    `ID_PRODUIT`        int(11)         DEFAULT NULL COMMENT 'Pour la gestion du stock',
    `Designation`       varchar(255)    NOT NULL,
    `Quantite`          decimal(10,2)   NOT NULL DEFAULT '1.00',
    `PrixU`  			decimal(24,2)   NOT NULL DEFAULT '0.00',
    `Taux_TVA`          decimal(5,2)    NOT NULL DEFAULT '0.00',
    `PrixHT` 			decimal(24,2)   NOT NULL DEFAULT '0.00',
    `Montant_Total_TTC` decimal(24,2)   NOT NULL DEFAULT '0.00',
	`ORDRE`    			int(11)         NOT NULL DEFAULT '0',
    `PFL_CRE`           varchar(50)     DEFAULT NULL,
    `DAT_CRE`           timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `PFL_MAJ`           varchar(50)     DEFAULT NULL,
    `DAT_MAJ`           timestamp       NULL DEFAULT NULL,
    `PFL_SUP`           varchar(50)     DEFAULT NULL,
    `DAT_SUP`           timestamp       NULL DEFAULT NULL,
    `SUPP`              tinyint(1)      DEFAULT '0',
    PRIMARY KEY (`ID_LIGNE_AVOIR`),
    CONSTRAINT `fk_ligne_avoir_entete` FOREIGN KEY (`ID_AVOIR_FOURN`) REFERENCES `facture_avoir_fournisseur` (`ID_AVOIR_FOURN`)
);

##
CREATE TRIGGER `%1`.`TR_DEVIS`
	AFTER INSERT ON `%1`.`bonlivraison`
	FOR EACH ROW 
	UPDATE devis SET Numero_Devis = CONCAT('DV-',DATE_FORMAT(CURDATE(), '%Y%m%d'), '-', ID_DEVIS) where ID_DEVIS = NEW.ID_DEVIS
##
CREATE TRIGGER trg_facture_update_etat
BEFORE UPDATE ON facture
FOR EACH ROW
BEGIN
    IF NEW.REGLEE = 1 THEN
        SET NEW.ID_ETAT_FACTURE = (
            SELECT ID_ETAT_FACTURE 
            FROM g_etat_facture 
            WHERE ETAT_CODE = 'PAY' 
            LIMIT 1
        );
    END IF;
END;
##
CREATE PROCEDURE GetStockData(
	IN condition_string VARCHAR(255), 
	IN limit_string VARCHAR(255)
	)
	BEGIN
	SET @query = CONCAT('
	SELECT
	SK.ID_STOCK,
	SK.Quantite,
	SK.DernirMVM,
	SK.ID_PRODUIT,
	SK.PFL_CRE,
	SK.DAT_CRE,
	SK.PFL_MAJ,
	SK.DAT_MAJ,
	SK.PFL_SUP,
	SK.DAT_SUP,
	PRD.SUPP,
	SK.Notes,
	PRD.CodeBarre,
	PRD.CodeBarre,		
	PRD.REFERENCE,
	PRD.DESIGNATION,
	PRD.Detail,
	PRD.Seuil_Alerte,
	PRD.PrixHT_ACHAT,
	PRD.TauxTVA,
	PRD.PrixTTC_ACHAT,
	PRD.Prix_Revient,
	PRD.Desc_Revient,
	PRD.PrixHT_VENTE,
	PRD.PrixTTC_VENTE,
	FR.ID_FOURNISSEUR,
	FR.SOCIETE
	FROM
	stock SK
	INNER JOIN produit PRD ON (PRD.ID_PRODUIT = SK.ID_PRODUIT)
	LEFT JOIN fournisseur FR on (FR.ID_FOURNISSEUR = PRD.ID_FOURNISSEUR)
	WHERE
	1 = 1 ', condition_string, '
	ORDER BY
	PRD.DESIGNATION ', limit_string);
	
	PREPARE statement FROM @query;
	EXECUTE statement;
	DEALLOCATE PREPARE statement;
	
	END;
##
	CREATE PROCEDURE RecupererTodayInfos()
	BEGIN
	
	DECLARE STAT_CMD INT;
	DECLARE STAT_DV INT;
	DECLARE STAT_PRD INT;
	DECLARE STAT_CLI INT;
	DECLARE STAT_FRN INT;
	
	DECLARE STAT_TOTAL_CMD INT;
	DECLARE STAT_TOTAL_DV INT;
	DECLARE STAT_TOTAL_PRD INT;
	DECLARE STAT_TOTAL_CLI INT;
	DECLARE STAT_TOTAL_FRN INT;
	DECLARE STAT_TOTAL_STK FLOAT;
	
	SELECT COUNT(ID_COMMANDE) INTO STAT_CMD FROM commande WHERE DateCommande = CURDATE() AND SUPP = 0;
	SELECT COUNT(ID_DEVIS) INTO STAT_DV FROM devis WHERE DateDevis = CURDATE() AND SUPP = 0;
	SELECT COUNT(ID_PRODUIT) INTO STAT_PRD FROM produit WHERE SUPP = 0 AND ( DAT_CRE BETWEEN  CURDATE() AND current_date + INTERVAL 1 DAY);
	SELECT COUNT(ID_CLIENT) INTO STAT_CLI FROM g_client WHERE SUPP = 0 AND ( DAT_CRE BETWEEN  CURDATE() AND current_date + INTERVAL 1 DAY);
	SELECT COUNT(ID_FOURNISSEUR) INTO STAT_FRN FROM fournisseur WHERE SUPP = 0 AND ( DAT_CRE BETWEEN  CURDATE() AND current_date + INTERVAL 1 DAY);
	
	SELECT COUNT(ID_COMMANDE) INTO STAT_TOTAL_CMD FROM commande WHERE SUPP = 0;
	SELECT COUNT(ID_DEVIS) INTO STAT_TOTAL_DV FROM devis WHERE SUPP = 0;
	SELECT COUNT(ID_PRODUIT) INTO STAT_TOTAL_PRD FROM produit WHERE SUPP = 0;  
	SELECT COUNT(ID_CLIENT) INTO STAT_TOTAL_CLI FROM g_client WHERE SUPP = 0;
	SELECT COUNT(ID_FOURNISSEUR) INTO STAT_TOTAL_FRN FROM fournisseur WHERE SUPP = 0;
	SELECT SUM(Quantite) INTO STAT_TOTAL_STK FROM stock WHERE SUPP = 0;
	
	SELECT STAT_CMD, STAT_DV, STAT_PRD, STAT_CLI, STAT_FRN, STAT_TOTAL_CMD, STAT_TOTAL_DV, STAT_TOTAL_PRD, STAT_TOTAL_CLI, STAT_TOTAL_FRN, STAT_TOTAL_STK;
	
	END;
##
CREATE TRIGGER `trg_after_insert_bonreception`
AFTER INSERT ON `bonreception`
FOR EACH ROW
BEGIN
    DECLARE v_montant    decimal(24,2) DEFAULT 0;
    DECLARE v_id_fourn   int(11);
    DECLARE v_etat_ovr   int(11);
    DECLARE v_id_dette   int(11);

    -- 1. Sécurité : On vérifie si l'ID_COMMANDE est présent
    IF NEW.ID_COMMANDE IS NOT NULL AND NEW.ID_COMMANDE > 0 THEN

        -- Mise à jour du numéro de commande
        UPDATE commande 
        SET Numero_Commande = CONCAT('CMD-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-', NEW.ID_COMMANDE)
        WHERE ID_COMMANDE = NEW.ID_COMMANDE;

        -- 2. Récupération des infos de la commande
        -- On utilise COALESCE pour éviter les valeurs NULL
        SELECT COALESCE(TotalTTC, 0), ID_FOURNISSEUR 
        INTO v_montant, v_id_fourn
        FROM commande 
        WHERE ID_COMMANDE = NEW.ID_COMMANDE;

        -- 3. Récupération de l'état 'Ouverte'
        SELECT ID_ETAT_DETTE INTO v_etat_ovr
        FROM g_etat_dette
        WHERE ETAT_CODE = 'OVR'
        LIMIT 1;

        -- 4. Création de la dette si montant > 0
        IF v_montant > 0 AND v_id_fourn IS NOT NULL AND v_etat_ovr IS NOT NULL THEN
            
            INSERT INTO dette_fournisseur (
                ID_FOURNISSEUR,
                ID_BonReception,
                DateDette,
                MontantTotal,
                MontantRegle,
                MontantRestant,
                ID_ETAT_DETTE,
                PFL_CRE
            ) VALUES (
                v_id_fourn,
                NEW.ID_BonReception,
                CURDATE(),
                v_montant,
                0,
                v_montant,
                v_etat_ovr,
                NEW.PFL_CRE
            );

            SET v_id_dette = LAST_INSERT_ID();

            -- Mise à jour du numéro de dette
            UPDATE dette_fournisseur
            SET Numero_Dette = CONCAT('DT-', DATE_FORMAT(CURDATE(), '%Y%m%d'), '-', LPAD(v_id_dette, 4, '0'))
            WHERE ID_DETTE = v_id_dette;
        END IF;
    END IF;
END;
##
CREATE TRIGGER `trg_after_reglement_dette`
AFTER INSERT ON `his_reglement_dette`
FOR EACH ROW
BEGIN
    -- 1. Déclaration des variables locales
    DECLARE v_total       DECIMAL(24,2);
    DECLARE v_regle       DECIMAL(24,2);
    DECLARE v_restant     DECIMAL(24,2);
    DECLARE v_echeance    DATE;
    DECLARE v_nouvel_etat INT(11);
    
    -- Variables pour stocker les IDs d'états
    DECLARE v_etat_pay    INT(11);
    DECLARE v_etat_ppay   INT(11);
    DECLARE v_etat_rtd    INT(11);

    -- 2. Récupération des données actuelles de la dette
    -- Utilisation de COALESCE pour éviter les erreurs de calcul avec des valeurs NULL
    SELECT 
        COALESCE(MontantTotal, 0), 
        COALESCE(MontantRegle, 0), 
        DateEcheance
    INTO 
        v_total, 
        v_regle, 
        v_echeance
    FROM dette_fournisseur
    WHERE ID_DETTE = NEW.ID_DETTE;

    -- 3. Récupération dynamique des IDs des états depuis le référentiel
    SELECT ID_ETAT_DETTE INTO v_etat_pay  FROM g_etat_dette WHERE ETAT_CODE = 'PAY' LIMIT 1;
    SELECT ID_ETAT_DETTE INTO v_etat_ppay FROM g_etat_dette WHERE ETAT_CODE = 'PPY' LIMIT 1;
    SELECT ID_ETAT_DETTE INTO v_etat_rtd  FROM g_etat_dette WHERE ETAT_CODE = 'RTD' LIMIT 1;

    -- 4. Calcul du nouveau cumul et du reste à payer
    SET v_regle   = v_regle + NEW.MontantReglement;
    SET v_restant = v_total - v_regle;

    -- 5. Logique de détermination de l'état
    IF v_restant <= 0 THEN
        -- Dette intégralement payée
        SET v_nouvel_etat = v_etat_pay;
        SET v_restant     = 0;
    ELSEIF v_echeance IS NOT NULL AND v_echeance < CURDATE() THEN
        -- Il reste de l'argent et la date est dépassée
        SET v_nouvel_etat = v_etat_rtd;
    ELSE
        -- Il reste de l'argent mais on est encore dans les temps
        SET v_nouvel_etat = v_etat_ppay;
    END IF;

    -- 6. Mise à jour de la table parente
    UPDATE dette_fournisseur
    SET 
        MontantRegle   = v_regle,
        MontantRestant = v_restant,
        ID_ETAT_DETTE  = v_nouvel_etat,
        PFL_MAJ        = NEW.PFL_CRE,
        DAT_MAJ        = CURRENT_TIMESTAMP
    WHERE ID_DETTE = NEW.ID_DETTE;

END;
##
CREATE TRIGGER `trg_after_update_reglement_dette`
AFTER UPDATE ON `his_reglement_dette`
FOR EACH ROW
BEGIN
    -- 1. Déclaration des variables locales
    DECLARE v_total       DECIMAL(24,2);
    DECLARE v_regle       DECIMAL(24,2);
    DECLARE v_restant     DECIMAL(24,2);
    DECLARE v_echeance    DATE;
    DECLARE v_nouvel_etat INT(11);
    
    -- Variables pour stocker les IDs d'états
    DECLARE v_etat_pay    INT(11);
    DECLARE v_etat_ppay   INT(11);
    DECLARE v_etat_rtd    INT(11);

    -- 2. Récupération des données actuelles de la dette
    SELECT 
        COALESCE(MontantTotal, 0), 
        COALESCE(MontantRegle, 0), 
        DateEcheance
    INTO 
        v_total, 
        v_regle, 
        v_echeance
    FROM dette_fournisseur
    WHERE ID_DETTE = NEW.ID_DETTE;

    -- 3. Récupération dynamique des IDs des états
    SELECT ID_ETAT_DETTE INTO v_etat_pay  FROM g_etat_dette WHERE ETAT_CODE = 'PAY' LIMIT 1;
    SELECT ID_ETAT_DETTE INTO v_etat_ppay FROM g_etat_dette WHERE ETAT_CODE = 'PPY' LIMIT 1;
    SELECT ID_ETAT_DETTE INTO v_etat_rtd  FROM g_etat_dette WHERE ETAT_CODE = 'RTD' LIMIT 1;

    -- 4. Calcul du nouveau cumul et du reste à payer (LA DIFFÉRENCE EST ICI)
    -- On annule l'ancien montant et on applique le nouveau
    SET v_regle   = v_regle - OLD.MontantReglement + NEW.MontantReglement;
    SET v_restant = v_total - v_regle;

    -- 5. Logique de détermination de l'état
    IF v_restant <= 0 THEN
        -- Dette intégralement payée
        SET v_nouvel_etat = v_etat_pay;
        SET v_restant     = 0;
    ELSEIF v_echeance IS NOT NULL AND v_echeance < CURDATE() THEN
        -- Il reste de l'argent et la date est dépassée
        SET v_nouvel_etat = v_etat_rtd;
    ELSE
        -- Il reste de l'argent mais on est encore dans les temps
        SET v_nouvel_etat = v_etat_ppay;
    END IF;

    -- 6. Mise à jour de la table parente
    UPDATE dette_fournisseur
    SET 
        MontantRegle   = v_regle,
        MontantRestant = v_restant,
        ID_ETAT_DETTE  = v_nouvel_etat,
        PFL_MAJ        = NEW.PFL_MAJ, -- J'utilise PFL_MAJ ici car c'est un UPDATE
        DAT_MAJ        = CURRENT_TIMESTAMP
    WHERE ID_DETTE = NEW.ID_DETTE;
END;
##
DROP TRIGGER IF EXISTS `trg_before_insert_avoir_fourn`;

CREATE TRIGGER `trg_before_insert_avoir_fourn`
BEFORE INSERT ON `facture_avoir_fournisseur`
FOR EACH ROW
BEGIN
    DECLARE v_prefix VARCHAR(20);
    DECLARE v_max_seq INT;
    DECLARE v_next_seq VARCHAR(4);

    -- 1. Générer le préfixe basé sur la Date de l'Avoir (ex: 'AV-F-202403-')
    -- On utilise NEW.DateAvoir (plutôt que la date du jour) pour respecter la date comptable
    SET v_prefix = CONCAT('FAV-F-', DATE_FORMAT(NEW.DateAvoir, '%Y%m'), '-');

    -- 2. On ne génère le numéro que s'il n'a pas été forcé manuellement lors du INSERT
    IF NEW.Numero_Avoir IS NULL OR NEW.Numero_Avoir = '' THEN
        
        -- 3. Chercher le plus grand numéro de séquence existant pour CE mois précis
        -- SUBSTRING_INDEX récupère la partie après le dernier tiret '-'
        SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(Numero_Avoir, '-', -1) AS UNSIGNED)), 0)
        INTO v_max_seq
        FROM facture_avoir_fournisseur
        WHERE Numero_Avoir LIKE CONCAT(v_prefix, '%');

        -- 4. Incrémenter et formater avec des zéros à gauche (ex: 1 devient '0001')
        SET v_next_seq = LPAD(v_max_seq + 1, 4, '0');

        -- 5. Assigner le numéro final à la colonne NEW.Numero_Avoir
        SET NEW.Numero_Avoir = CONCAT(v_prefix, v_next_seq);
        
    END IF;
END;
