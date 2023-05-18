<?php

namespace App\Entity;

enum Role: string {
    // Admin
    case ROLE_ADMIN = "ROLE_ADMIN";
    // Utilisateurs
    case ROLE_USER_LIST = "ROLE_USER_LIST";
    case ROLE_USER_EDIT = "ROLE_USER_EDIT";
    // Chantiers
    case ROLE_CHANTIER_LIST = "ROLE_CHANITER_LIST";
    case ROLE_CHANTIER_EDIT = "ROLE_CHANITER_EDIT";
    // Références
    case ROLE_REFERENCE_LIST = "ROLE_REFERENCE_LIST";
    case ROLE_REFERENCE_EDIT = "ROLE_REFERENCE_EDIT";
    case ROLE_REFERENCE_STOCK = "ROLE_REFERENCE_STOCK";

}
