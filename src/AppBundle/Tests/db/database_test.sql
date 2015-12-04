create database if not exists kzya_billetsdulouvre_test character set utf8 collate utf8_unicode_ci;
use kzya_billetsdulouvre_test;

grant all privileges on kzya_billetsdulouvre_test.* to 'kzya_louvre'@'localhost' identified by 'mdp_Louvre';
