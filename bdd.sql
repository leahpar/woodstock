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

-- Check paniers brouillons
SELECT p.user_id, p.type, count(distinct p.id), count(distinct s.id)
FROM panier p
left join stock s on s.panier_id = p.id
WHERE p.brouillon = 1
group by 1, 2
ORDER BY p.user_id ASC, p.type asc, p.date asc;

SELECT p.* , count(distinct s.id)
FROM panier p
left join stock s on s.panier_id = p.id
WHERE p.brouillon = 1
group by p.id
ORDER BY p.user_id ASC, p.type asc, p.date asc;

insert into materiel (nom, reference, categorie)
select r.reference, r.nom, 'Echafaudage' from reference r
where categorie = 'ECHAFAUDAGE';

delete from reference where categorie = 'ECHAFAUDAGE';


update reference set essence = 'douglas' where essence = 'Douglas';
update reference set essence = 'lamelles' where essence = 'Lamellés';
update reference set essence = 'ossature' where essence = 'Ossature';
update reference set essence = 'charpente' where essence = 'Charpente';
