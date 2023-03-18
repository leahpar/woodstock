<?php

namespace App\Entity;

enum Role: string {
    case ROLE_ADMIN = "ROLE_ADMIN";
    case ROLE_USER_LIST = "ROLE_USER_LIST";
    case ROLE_USER_EDIT = "ROLE_USER_EDIT";

}
