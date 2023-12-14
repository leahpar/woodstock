update user u
left join chantier c on c.conducteur_travaux_id = u.id
set u.conducteur_travaux = 1
where c.conducteur_travaux_id is not null

-- -----------------------------
-- Remise à zéro du stock
-- -----------------------------

delete from panier;

-- -----------------------------
-- MAJ historique des prix
-- -----------------------------

update stock s
left join reference r on s.reference_id = r.id
set s.prix = r.prix;

-- -----------------------------
-- Fusion chantiers
-- -----------------------------

-- 111 => 110
update panier set chantier_id = 110 where chantier_id = 111;
delete from chantier where id = 111;
