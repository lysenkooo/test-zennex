<?php
/**
 * Message class is model for objects, that can be found in db table messages
 */
class Message
{
    private $_dbh = null;

    public function __construct() {
        $this->_dbh = Database::getInstance();
    }

    public function getLastMessage($offset = 0)
    {
        $sth = $this->_dbh->prepare('SELECT * FROM messages ORDER BY time DESC LIMIT :offset,1');
        $sth->bindValue(':offset', $offset, PDO::PARAM_INT);
        $sth->execute();
        if ( $result = $sth->fetch(PDO::FETCH_ASSOC) ) {
            $result['liked'] = explode(',', $result['liked']);
            $result['likes'] = count($result['liked']) - 1;
        }
        return $result;
    }

    public function addMessage($user, $text)
    {
        $sth = $this->_dbh->prepare('INSERT INTO messages (time, user, text) VALUES (NOW(), :user, :text)');
        $sth->execute(array(':user' => $user, ':text' => $text));
    }

    public function deleteMessage($id, $user)
    {
        $sth = $this->_dbh->prepare('DELETE FROM messages WHERE id=:id AND user=:user');
        $sth->execute(array(':id' => $id, ':user' => $user));
    }

    public function likeMessage($id, $user)
    {
        $sth = $this->_dbh->prepare('UPDATE messages
                                     SET liked = CONCAT(liked,:user)
                                     WHERE id=:id
                                     AND user<>:user
                                     AND LOCATE(:user, liked) = 0');
        $sth->execute(array(':id' => $id, ':user' => $user . ','));
    }

    public function unlikeMessage($id, $user)
    {
        $sth = $this->_dbh->prepare('UPDATE messages SET liked = REPLACE(liked,:user,\'\') WHERE id=:id');
        $sth->execute(array(':id' => $id, ':user' => $user . ','));
    }

}
