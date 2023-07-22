<?php

namespace App\Entity;

enum Role: string {
    // Admin
    case ROLE_ADMIN = "ROLE_ADMIN";
    // Compta
    case ROLE_COMPTA = "ROLE_COMPTA";
    // Utilisateurs
    case ROLE_USER_LIST = "ROLE_USER_LIST";
    case ROLE_USER_EDIT = "ROLE_USER_EDIT";
    // Chantiers
    case ROLE_CHANTIER_LIST = "ROLE_CHANITER_LIST";
    case ROLE_CHANTIER_EDIT = "ROLE_CHANITER_EDIT";
    // Matériel
    case ROLE_MATERIEL_LIST = "ROLE_MATERIEL_LIST";
    case ROLE_MATERIEL_EDIT = "ROLE_MATERIEL_EDIT";
    // Certificat
    case ROLE_CERTIFICAT_LIST = "ROLE_CERTIFICAT_LIST";
    case ROLE_CERTIFICAT_EDIT = "ROLE_CERTIFICAT_EDIT";
    // Références
    case ROLE_REFERENCE_LIST = "ROLE_REFERENCE_LIST";
    case ROLE_REFERENCE_EDIT = "ROLE_REFERENCE_EDIT";
    case ROLE_REFERENCE_STOCK = "ROLE_REFERENCE_STOCK";

}
