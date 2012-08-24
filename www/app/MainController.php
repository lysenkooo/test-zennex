<?php
/**
 * MainContoller class contains action handlers for regular behavior
 */
class MainController
{
    private $_model;

    public function __construct() {
        $this->_model = new Message;
    }

    private function _redirect() {
        header('Location: ./');
    }

    private function _getUser() {
        if ( isset($_COOKIE['user']) ) {
            $user = substr($_COOKIE['user'], 0, 20);
            $user = strip_tags($user);
            $user = htmlspecialchars($user);
            // protection for like field
            $user = str_replace(',', '', $user);
            // mysql_escape_string is unnecessary because we use PDO::prepare everywhere
            return $user;
        } else {
            return false;
        }
    }

    public function logIn() {
        if ( isset($_POST['message']['user']) && $this->_getUser() === false ) {
            setcookie('user', $_POST['message']['user']);
            return true;
        } else {
            return false;
        }
    }

    public function logoutAction() {
        if ( $this->_getUser() !== false ) {
            setcookie('user', null, 0);
        }
        $this->_redirect();
    }

    public function showAction() {
        $isMore = true;
        $messages = array();
        $show = ( isset($_GET['show']) && (int) $_GET['show'] > 10 ) ? (int) $_GET['show'] : 10;
        for ( $i = 0; $i < $show; $i++ ) {
            if ( !($messages[] = $this->_model->getLastMessage($i)) ) {
                array_pop( $messages );
                $isMore = false;
                break;
            }
        }

        // May be we got all elements, but isMore still true?
        if ( $isMore && !$this->_model->getLastMessage($i+1) ) {
            $isMore = false;
        }

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) {
            // ajax behavior detected
            $viewLayout = 'articles';
            $viewParts = array();
        } else {
            // regular behavior (e.g. js disabled)
            $viewLayout = 'layout';
            $viewParts = array('articles' => 'articles');
        }

        $view = new View($viewLayout);
        $view->assign('messages', $messages);
        $view->assign('isMore', $isMore);
        $view->assign('user', $this->_getUser());
        $view->render($viewParts);

    }

    public function addAction() {
        // check auth info
        if ( $this->_getUser() !== false || $this->logIn() ) {
            // for cases when message contain only attaches
            if ( !isset($_POST['message']['text']) ) {
                $_POST['message']['text'] = '';
            }
            // remove bullshit
            $messageText = substr($_POST['message']['text'], 0, 1000);
            $messageText = strip_tags($messageText);
            $messageText = htmlspecialchars($messageText);
            $messageText = str_replace("\r\n", "<br>\r\n", $messageText);
            $messageText = str_replace("\n", "<br>\n", $messageText);
            // link attach support
            $pattern = '~\bhttp://(?!www.youtube.com)[\w\d-.]+\b~';
            $replacement = '<a href="$0">$0</a>';
            $messageText = preg_replace($pattern, $replacement, $messageText);
            // youtube attach support
            $pattern = '~\bhttp://www\.youtube\.com/watch\?v=(.{11}).*\b~';
            $replacement = '<br><iframe width="420" height="315" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe><br>' . PHP_EOL;
            $messageText = preg_replace($pattern, $replacement, $messageText);
            // file attach support
            for ($i = 0; $i < 3; $i++) {
                if ( empty($_FILES['attach']['size'][$i])
                    || $_FILES['attach']['size'][$i] > 100000 ) {
                    continue;
                }
                if ( is_uploaded_file($_FILES['attach']['tmp_name'][$i]) ) {
                    $dest = 'static/upload/' . time() . '-' . $_FILES['attach']['name'][$i];
                    move_uploaded_file($_FILES['attach']['tmp_name'][$i], $dest);
                }
                $messageText .= '<br><img alt="Attachment" src="' . $dest . '">' . PHP_EOL;
            }

            // if user just logged in and messageText is really empty
            if (!empty($messageText)) {
                $this->_model->addMessage( $this->_getUser(), $messageText );
            }
        }

        $this->_redirect();
    }

    public function likeAction() {
        if ( $this->_getUser() !== false ) {
            $this->_model->likeMessage( (int) $_GET['id'], $this->_getUser() );
        }
        $this->_redirect();
    }

    public function unlikeAction() {
        if ( $this->_getUser() !== false ) {
            $this->_model->unlikeMessage( (int) $_GET['id'], $this->_getUser() );
        }
        $this->_redirect();
    }

    public function deleteAction() {
        if ( $this->_getUser() !== false ) {
            $this->_model->deleteMessage( (int) $_GET['id'], $this->_getUser() );
        }
        $this->_redirect();
    }
}
