<?php
use PHPUnit\Framework\TestCase;
use Application\Mail;

class MailTest extends TestCase {
    protected PDO $pdo;

    protected function setUp(): void
    {
        $dsn = "pgsql:host=" . getenv('DB_TEST_HOST') . ";dbname=" . getenv('DB_TEST_NAME');
        $this->pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'));
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("DROP TABLE IF EXISTS mail;");
        $this->pdo->exec("
            CREATE TABLE mail (
                id SERIAL PRIMARY KEY,
                subject TEXT NOT NULL,
                body TEXT NOT NULL
            );
        ");
    }

    public function testCreateMail() {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Alice", "Hello world");

        $this->assertIsInt($id);
        $this->assertEquals(1, $id);
    }

    public function testGetMail() {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Test", "Body");

        $result = $mail->getMail($id);
        $this->assertEquals("Test", $result['subject']);
    }

    public function testGetAllMail() {
        $mail = new Mail($this->pdo);
        $mail->createMail("A", "Body A");
        $mail->createMail("B", "Body B");

        $result = $mail->getAllMail();
        $this->assertCount(2, $result);
    }

    public function testUpdateMail() {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Old", "Old body");

        $rows = $mail->updateMail($id, "New", "New body");
        $this->assertEquals(1, $rows);

        $updated = $mail->getMail($id);
        $this->assertEquals("New", $updated['subject']);
    }

    public function testDeleteMail() {
        $mail = new Mail($this->pdo);
        $id = $mail->createMail("Delete", "Me");

        $rows = $mail->deleteMail($id);
        $this->assertEquals(1, $rows);

        $this->assertFalse($mail->getMail($id));
    }
}
