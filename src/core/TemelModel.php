<?php
/**
 * src/core/TemelModel.php
 * Tüm model sınıflarının miras aldığı soyut temel sınıf.
 * OOP: Abstraction + Inheritance
 */
require_once __DIR__ . '/Veritabani.php';

abstract class TemelModel {
    protected PDO    $db;
    protected string $tablo = '';

    public function __construct() {
        $this->db = Veritabani::baglan();
    }

    /** ID ile tek kayıt getirir */
    public function idIleGetir(int $id): ?array {
        try {
            $s = $this->db->prepare("SELECT * FROM {$this->tablo} WHERE id = ? LIMIT 1");
            $s->execute([$id]);
            return $s->fetch() ?: null;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /** Tüm kayıtları getirir */
    public function hepsiniGetir(): array {
        try {
            return $this->db->query("SELECT * FROM {$this->tablo}")->fetchAll();
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }

    /** ID ile siler */
    public function sil(int $id): bool {
        try {
            $s = $this->db->prepare("DELETE FROM {$this->tablo} WHERE id = ?");
            return $s->execute([$id]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
