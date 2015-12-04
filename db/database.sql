create database if not exists kzya_billetsdulouvre character set utf8 collate utf8_unicode_ci;
use kzya_billetsdulouvre;

grant all privileges on kzya_billetsdulouvre.* to 'kzya_louvre'@'localhost' identified by 'mdp_Louvre';
