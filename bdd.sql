update user u
left join chantier c on c.conducteur_travaux_id = u.id
set u.conducteur_travaux = 1
where c.conducteur_travaux_id is not null

-- -----------------------------
-- Remise à zéro du stock
-- -----------------------------

delete from panier;
