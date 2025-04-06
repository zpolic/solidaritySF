[![Symfony Tests](../../actions/workflows/symfony.yml/badge.svg)](../../actions/workflows/symfony.yml)

# Mreža Solidarnosti
Mreža solidarnosti je inicijativa IT Srbije za direktnu finansijsku podršku nastavnicima i vannastavnom osoblju čija je plata umanjena zbog obustave rada.

![image.jpg](public/image/readme.png)

## Tehnologije

- [PHP 8.3](https://www.php.net/)
- [NGINX](https://nginx.org/)
- [MySQL 8](https://www.mysql.com/)
- [Docker](https://www.docker.com/)
- [Symfony 6.4](https://symfony.com/)
- [TailwindCSS 4](https://tailwindcss.com/)
- [daisyUI 4](https://daisyui.com/)
- [Tabler Icons](https://tabler.io/icons)

---

## Instalacija projekta

Pratite sledeće korake kako biste pokrenuli projekat lokalno:

1. Klonirajte projekat
```bash
$ git clone https://github.com/IT-Srbija-Org/solidaritySF; 
cd solidaritySF;
```

2. Pokrenite skriptu za konfiguraciju
```bash
$ ./configureProject.sh
```

3. Otvorite URL adresu u pretraživaču: http://localhost:1000

---

## Test korisnici

Test korisnici se automatski kreiraju pomoću [DoctrineFixturesBundle](https://symfony.com/bundles/DoctrineFixturesBundle) tokom pokretanja skripte za konfiguraciju.

| Email              | Lozinka   | Privilegije |
|--------------------|-----------|-------------|
| korisnik@gmail.com | 12345678  | ROLE_USER   |
| admin@gmail.com    | 12345678  | ROLE_ADMIN  |