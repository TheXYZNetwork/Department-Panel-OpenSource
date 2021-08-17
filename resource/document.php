<?php
require_once("handler/database.php");

function CreateDocument($depID, $title, $desc, $content, $viewability, $interaction, $creator) {
    global $MainDatabase;;
    global $cache;

    $depID = $MainDatabase->real_escape_string($depID);
    $title = $MainDatabase->real_escape_string($title);
    $desc = $MainDatabase->real_escape_string($desc);
    $contents = $MainDatabase->real_escape_string($content);
    $viewability = json_encode($viewability);
    $viewability = $MainDatabase->real_escape_string($viewability);
    $interaction = $interaction ? 1 : false;
    $creator = $MainDatabase->real_escape_string($creator);
    $time = time();

    if ($interaction) {
        $result = $MainDatabase->query("INSERT INTO documents(userid, department_id, title, description, content, viewability, interaction, created) VALUES ('$creator', $depID, '$title', '$desc', '$contents', '$viewability', $interaction, $time)");
    } else {
        $result = $MainDatabase->query("INSERT INTO documents(userid, department_id, title, description, content, viewability, created) VALUES ('$creator', $depID, '$title', '$desc', '$contents', '$viewability', $time)");
    }

    if (!$result) {
        echo $MainDatabase->error;
    }

    $docID = $MainDatabase->insert_id;

    // We create an internal inital revision here. It's simply a clone of the inital document, but it kick starts the revision histroy.
    CreateDocumentRevision($docID, $creator, $content);

    $cache->remove("docs-all");
    $cache->remove("docs-dep-$depID");

    return $docID;
}

function CreateDocumentRevision($docID, $userID, $revision) {
    global $MainDatabase;;
    global $cache;

    $docID = $MainDatabase->real_escape_string($docID);
    $userID = $MainDatabase->real_escape_string($userID);
    $revision = $MainDatabase->real_escape_string($revision);
    $time = time();

    $result = $MainDatabase->query("INSERT INTO documents_revisions(document_id, userid, revision, created) VALUES ($docID, '$userID', '$revision', $time)");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("docs-rev-$docID");
    $cache->remove("docs-$docID");
}

function GetDocument($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("docs-$id")) {
        return $cache->get("docs-$id");
    }

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("SELECT * FROM documents WHERE id = '$id' LIMIT 1;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("docs-$id", $result, 600);

    return $result;
}

function GetDocumentRevisions($docID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("docs-rev-$docID")) {
        return $cache->get("docs-rev-$docID");
    }

    $id = $MainDatabase->real_escape_string($docID);

    $result = $MainDatabase->query("SELECT * FROM documents_revisions WHERE document_id = '$id' ORDER BY created DESC;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("docs-rev-$docID", $result, 600);

    return $result;
}
function GetDocumentRevision($revID)
{
    global $MainDatabase;
    global $cache;

    if ($cache->has("docs-$revID-rev")) {
        return $cache->get("docs-$revID-rev");
    }

    $id = $MainDatabase->real_escape_string($revID);

    $result = $MainDatabase->query("SELECT * FROM documents_revisions WHERE id = '$id';");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("docs-$revID-rev", $result, 600);

    return $result;
}

function GetDocumentComments($docID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("docs-comments-$docID")) {
        return $cache->get("docs-comments-$docID");
    }

    $id = $MainDatabase->real_escape_string($docID);

    $result = $MainDatabase->query("SELECT * FROM comments_documents WHERE document_id = '$id' ORDER BY created DESC;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("docs-comments-$docID", $result, 600);

    return $result;
}
function AddDocumentComment($docID, $commenter, $comment) {
    global $MainDatabase;
    global $cache;

    $docID = $MainDatabase->real_escape_string($docID);
    $commenter = $MainDatabase->real_escape_string($commenter);
    $comment = $MainDatabase->real_escape_string($comment);
    $time = time();

    $MainDatabase->query("INSERT INTO comments_documents(document_id, userid, comment, created) VALUES ($docID, '$commenter', '$comment', $time)");

    $cache->remove("docs-comments-$docID");
}

function GetAllDocumentsForDepartment($depID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("docs-dep-$depID")) {
        return $cache->get("docs-dep-$depID");
    }

    $id = $MainDatabase->real_escape_string($depID);

    $result = $MainDatabase->query("SELECT id FROM documents WHERE department_id = '$id' AND (deleted <> '1' OR deleted IS NULL) ;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("docs-dep-$id", $result, 600);

    return $result;
}

function UpdateDocumentTitle($id, $userID, $title) {
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $title = $MainDatabase->real_escape_string($title);
    $time = time();

    $result = $MainDatabase->query("UPDATE documents SET title = '$title', modified = '$time' WHERE id = $id");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("docs-$id");
}

function UpdateDocumentDesc($id, $userID, $desc) {
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $desc = $MainDatabase->real_escape_string($desc);
    $time = time();

    $result = $MainDatabase->query("UPDATE documents SET description = '$desc', modified = '$time' WHERE id = $id");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("docs-$id");
}

function UpdateDocumentContents($id, $userID, $content)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $contents = $MainDatabase->real_escape_string($content);
    $time = time();

    $result = $MainDatabase->query("UPDATE documents SET content = '$contents', modified = '$time' WHERE id = $id");

    if (!$result) {
        echo $MainDatabase->error;
    }

    CreateDocumentRevision($id, $userID, $content);

    $cache->remove("docs-$id");
}

function UpdateDocumentViewability($id, $userID, $viewability)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $viewability = json_encode($viewability);
    $viewability = $MainDatabase->real_escape_string($viewability);
    $time = time();

    $result = $MainDatabase->query("UPDATE documents SET viewability = '$viewability', modified = '$time' WHERE id = $id");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("docs-$id");
}

function UpdateDocumentInteractibility($id, $userID, $interaction)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $interaction = $interaction ? 1 : 0;
    $time = time();

    $result = $MainDatabase->query("UPDATE documents SET interaction = '$interaction', modified = '$time' WHERE id = $id");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("docs-$id");

    return true;
}

function UpdateDocumentPublished($id, $userID, $publish)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $publish = $publish ? 1 : 0;
    $time = time();

    $result = $MainDatabase->query("UPDATE documents SET published = '$publish', modified = '$time' WHERE id = $id");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("docs-$id");

    return true;
}

function UpdateDocumentDeleted($id, $userID, $deleted, $depID)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $deleted = $deleted ? 1 : 0;
    $time = time();

    $MainDatabase->query("UPDATE documents SET deleted = '$deleted', modified = '$time' WHERE id = $id");

    $cache->remove("docs-$id");
    $cache->remove("docs-dep-$depID");
    $cache->remove("docs-rev-$id");

    return true;
}