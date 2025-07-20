<?php
/**
 * Mail Migration Tool - IMAP Connector Class
 * Clase para manejar conexiones IMAP usando ddeboer/imap
 */

namespace MailMigration;

use Ddeboer\Imap\Server;
use Ddeboer\Imap\Connection;
use Ddeboer\Imap\Exception\AuthenticationFailedException;
use Ddeboer\Imap\Exception\ImapGetmailboxesException;
use Exception;

class ImapConnector
{
    private $config;
    private $sourceConnection = null;
    private $destinationConnection = null;
    
    public function __construct($config = null)
    {
        $this->config = $config ?: require_once __DIR__ . '/../config/config.php';
    }
    
    /**
     * Test connection to an IMAP server
     *
     * @param array $serverConfig Server configuration
     * @return array Result with success status and message
     */
    public function testConnection($serverConfig)
    {
        try {
            $server = new Server(
                $serverConfig['host'],
                $serverConfig['port'],
                $serverConfig['ssl'] ? '/ssl' : null
            );
            
            // Attempt authentication
            $connection = $server->authenticate($serverConfig['username'], $serverConfig['password']);
            
            // Get mailbox count for verification
            $mailboxes = $connection->getMailboxes();
            $mailboxCount = count($mailboxes);
            
            // Test accessing INBOX
            $inbox = $connection->getMailbox('INBOX');
            $messageCount = $inbox->count();
            
            $connection->close();
            
            return [
                'success' => true,
                'message' => "Conexión exitosa!\n• Servidor: {$serverConfig['host']}:{$serverConfig['port']}\n• SSL: " . ($serverConfig['ssl'] ? 'Sí' : 'No') . "\n• Carpetas encontradas: {$mailboxCount}\n• Mensajes en INBOX: {$messageCount}",
                'mailbox_count' => $mailboxCount,
                'message_count' => $messageCount
            ];
            
        } catch (AuthenticationFailedException $e) {
            return [
                'success' => false,
                'message' => "Error de autenticación:\n• Verificar usuario y contraseña\n• Servidor: {$serverConfig['host']}:{$serverConfig['port']}"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Error de conexión:\n• " . $e->getMessage() . "\n• Verificar host, puerto y configuración SSL"
            ];
        }
    }
    
    /**
     * Connect to source IMAP server
     *
     * @param array $serverConfig Server configuration
     * @return bool Success status
     */
    public function connectSource($serverConfig)
    {
        try {
            $server = new Server(
                $serverConfig['host'],
                $serverConfig['port'],
                $serverConfig['ssl'] ? '/ssl' : null
            );
            
            $this->sourceConnection = $server->authenticate(
                $serverConfig['username'], 
                $serverConfig['password']
            );
            
            return true;
        } catch (Exception $e) {
            $this->logError("Error connecting to source: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Connect to destination IMAP server
     *
     * @param array $serverConfig Server configuration
     * @return bool Success status
     */
    public function connectDestination($serverConfig)
    {
        try {
            $server = new Server(
                $serverConfig['host'],
                $serverConfig['port'],
                $serverConfig['ssl'] ? '/ssl' : null
            );
            
            $this->destinationConnection = $server->authenticate(
                $serverConfig['username'], 
                $serverConfig['password']
            );
            
            return true;
        } catch (Exception $e) {
            $this->logError("Error connecting to destination: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get mailboxes from source server
     *
     * @return array List of mailboxes with details
     */
    public function getSourceMailboxes()
    {
        if (!$this->sourceConnection) {
            throw new Exception("Source connection not established");
        }
        
        $mailboxes = [];
        foreach ($this->sourceConnection->getMailboxes() as $mailbox) {
            $mailboxes[] = [
                'name' => $mailbox->getName(),
                'count' => $mailbox->count()
            ];
        }
        
        return $mailboxes;
    }
    
    /**
     * Get mailboxes from destination server
     *
     * @return array List of mailboxes with details
     */
    public function getDestinationMailboxes()
    {
        if (!$this->destinationConnection) {
            throw new Exception("Destination connection not established");
        }
        
        $mailboxes = [];
        foreach ($this->destinationConnection->getMailboxes() as $mailbox) {
            $mailboxes[] = [
                'name' => $mailbox->getName(),
                'count' => $mailbox->count()
            ];
        }
        
        return $mailboxes;
    }
    
    /**
     * Create mailbox on destination server if it doesn't exist
     *
     * @param string $mailboxName Name of mailbox to create
     * @return bool Success status
     */
    public function createDestinationMailbox($mailboxName)
    {
        if (!$this->destinationConnection) {
            throw new Exception("Destination connection not established");
        }
        
        try {
            // Check if mailbox already exists
            $existing = $this->destinationConnection->getMailbox($mailboxName);
            return true; // Already exists
        } catch (Exception $e) {
            // Mailbox doesn't exist, try to create it
            try {
                $this->destinationConnection->createMailbox($mailboxName);
                return true;
            } catch (Exception $createException) {
                $this->logError("Failed to create mailbox {$mailboxName}: " . $createException->getMessage());
                return false;
            }
        }
    }
    
    /**
     * Migrate messages from source mailbox to destination mailbox
     *
     * @param string $sourceMailbox Source mailbox name
     * @param string $destinationMailbox Destination mailbox name
     * @param int $batchSize Number of messages to process per batch
     * @param bool $preserveFlags Whether to preserve message flags
     * @return array Migration result
     */
    public function migrateMailbox($sourceMailbox, $destinationMailbox, $batchSize = 50, $preserveFlags = true)
    {
        if (!$this->sourceConnection || !$this->destinationConnection) {
            throw new Exception("Source and destination connections must be established");
        }
        
        try {
            $source = $this->sourceConnection->getMailbox($sourceMailbox);
            $destination = $this->destinationConnection->getMailbox($destinationMailbox);
            
            $messages = $source->getMessages();
            $totalMessages = count($messages);
            $migratedCount = 0;
            $errorCount = 0;
            
            // Process all messages up to batch size limit
            $maxMessages = min($batchSize, $totalMessages);
            $processedCount = 0;
            
            foreach ($messages as $message) {
                if ($processedCount >= $maxMessages) {
                    break; // Stop after processing maxMessages
                }
                try {
                    // Get full message content
                    $rawMessage = $message->getRawMessage();
                    
                    // Get message date safely
                    try {
                        $messageDate = $message->getDate();
                        // Convert DateTimeImmutable to DateTime if needed
                        if ($messageDate instanceof \DateTimeImmutable) {
                            $messageDate = \DateTime::createFromImmutable($messageDate);
                        } elseif (!($messageDate instanceof \DateTime)) {
                            // If it's a string or other format, create DateTime normally
                            $messageDate = new \DateTime($messageDate);
                        }
                    } catch (Exception $dateEx) {
                        $messageDate = new \DateTime(); // Use current date if date parsing fails
                        $this->logError("Date parsing failed for message " . ($processedCount + 1) . ", using current date");
                    }
                    
                    // Build flags string if preservation is enabled
                    $flags = [];
                    if ($preserveFlags) {
                        // Debug: Log flag detection
                        $isSeen = $message->isSeen();
                        $isUnseen = $message->isUnseen();
                        $isFlagged = $message->isFlagged();
                        $isAnswered = $message->isAnswered();
                        $isDraft = $message->isDraft();
                        
                        $this->logError("DEBUG: Message " . ($processedCount + 1) . " flags - Seen: " . ($isSeen ? 'YES' : 'NO') . ", Unseen: " . ($isUnseen ? 'YES' : 'NO') . ", Flagged: " . ($isFlagged ? 'YES' : 'NO'));
                        
                        if ($isSeen) {
                            $flags[] = '\\Seen';
                        }
                        if ($isFlagged) {
                            $flags[] = '\\Flagged';
                        }
                        if ($isAnswered) {
                            $flags[] = '\\Answered';
                        }
                        if ($isDraft) {
                            $flags[] = '\\Draft';
                        }
                        // Note: We skip \\Deleted as we don't want to migrate deleted messages
                    }
                    
                    $flagsString = !empty($flags) ? implode(' ', $flags) : null;
                    $this->logError("DEBUG: Flags to apply: " . ($flagsString ?: 'NONE'));
                    
                    // Append to destination with preserved flags
                    $destination->addMessage($rawMessage, $flagsString, $messageDate);
                    
                    $migratedCount++;
                    
                } catch (Exception $e) {
                    $errorCount++;
                    $this->logError("Failed to migrate message " . ($processedCount + 1) . ": " . $e->getMessage());
                }
                
                $processedCount++;
                
                // Progress reporting could be added here
            }
            
            return [
                'success' => true,
                'total' => $maxMessages, // Show actual processed count
                'migrated' => $migratedCount,
                'errors' => $errorCount,
                'message' => "Migration completed: {$migratedCount}/{$maxMessages} messages migrated"
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Migration failed: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Close all connections
     */
    public function closeConnections()
    {
        if ($this->sourceConnection) {
            $this->sourceConnection->close();
            $this->sourceConnection = null;
        }
        
        if ($this->destinationConnection) {
            $this->destinationConnection->close();
            $this->destinationConnection = null;
        }
    }
    
    /**
     * Log error message
     *
     * @param string $message Error message
     */
    private function logError($message)
    {
        $logFile = $this->config['log_file'] ?? 'logs/migration.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] ERROR: {$message}" . PHP_EOL;
        
        // Ensure logs directory exists
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Get connection status
     *
     * @return array Connection status
     */
    public function getConnectionStatus()
    {
        return [
            'source_connected' => $this->sourceConnection !== null,
            'destination_connected' => $this->destinationConnection !== null
        ];
    }
} 