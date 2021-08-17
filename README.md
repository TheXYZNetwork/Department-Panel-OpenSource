# Department Panel
This is a reupload of the Department panel used for the main PoliceRP server. It is a complete upload. The only reason the original wasn't just unprivated is because there is sensitive information in the git history.

## Table creation statements
```
CREATE TABLE `users` (
 `userid` varchar(32) NOT NULL,
 `name` text NOT NULL,
 `avatar` text NOT NULL,
 `banned` int(1),
 `lastseen` int(32) NOT NULL,
 `joined` int(32) NOT NULL,
 PRIMARY KEY (`userid`)
)
```

```
CREATE TABLE `sessions` (
 `userid` varchar(32) NOT NULL,
 `token` varchar(32) NOT NULL,
 `created` int(32) NOT NULL
)
```

```
CREATE TABLE `departments` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(32) NOT NULL,
 `deleted` int(1),
 `government` int(1),
 `identifier` varchar(32),
 `created` int(32) NOT NULL,
 `modified` int(32),
 PRIMARY KEY (`id`)
) 
```

```
CREATE TABLE `departments_jobs` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `department_id` int(32) NOT NULL,
 `job` varchar(32) NOT NULL,
 `sorting` int(16) NOT NULL,
 `higherup` int(1),
 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)
) 
```

```
CREATE TABLE `comments_logs` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `log_id` int(11) NOT NULL,
 `userid` varchar(32) NOT NULL,
 `comment` varchar(1024) NOT NULL,
 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`)
) 
```

```
CREATE TABLE `comments_activity` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `activity_id` int(11) NOT NULL,
 `userid` varchar(32) NOT NULL,
 `comment` varchar(1024) NOT NULL,
 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`)
) 
```

```
CREATE TABLE `comments_documents` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `document_id` int(11) NOT NULL,
 `userid` varchar(32) NOT NULL,
 `comment` varchar(1024) NOT NULL,
 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`)
) 
```

```
CREATE TABLE `departments_tags` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `department_id` int(32) NOT NULL,
 `name` varchar(32) NOT NULL,
 `slug` varchar(6) NOT NULL,
 `color` varchar(16) NOT NULL,
 `expires` int(1),
 `deleted` int(1),
 `created` int(32) NOT NULL,
 `modified` int(32) NOT NULL,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)
) 
```

```
CREATE TABLE `members_tags` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `userid` varchar(32) NOT NULL,
 `department_id` int(32) NOT NULL,
 `tag_id` int(32) NOT NULL,
 `expires` int(32),
 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`),
 FOREIGN KEY (`tag_id`) REFERENCES `departments_tags`(`id`)
) 
```

```
CREATE TABLE `members_points` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `userid` varchar(32) NOT NULL,
 `department_id` int(32) NOT NULL,
 `officer_id` varchar(32) NOT NULL,
 `amount` int(11) NOT NULL,
 `reason` varchar(60) NOT NULL,
 `deleted` int(1),
 `expires` int(32),
 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)
) 
```

```
CREATE TABLE `comments_points` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `points_id` int(11) NOT NULL,
 `userid` varchar(32) NOT NULL,
 `comment` varchar(1024) NOT NULL,
 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`points_id`) REFERENCES `members_points`(`id`)
) 
```

```
CREATE TABLE `documents` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `userid` varchar(32) NOT NULL,
 `department_id` int(11) NOT NULL,
 `title` varchar(128) NOT NULL,
 `description` varchar(500) NOT NULL,
 `content` longtext NOT NULL,
 `viewability` text NOT NULL,
 `interaction` int(1),
 `published` int(1),
 `deleted` int(1),
 `created` int(32) NOT NULL,
 `modified` int(32),
 
 PRIMARY KEY (`id`),
 FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)
) 
```

```
CREATE TABLE `documents_revisions` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `document_id` int(11) NOT NULL,
 
 `userid` varchar(32) NOT NULL,
 `revision` longtext NOT NULL,
 
 `created` int(32) NOT NULL,
 
 PRIMARY KEY (`id`),
 FOREIGN KEY (`document_id`) REFERENCES `documents`(`id`)
) 
```

```
CREATE TABLE `meetings` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `department_id` int(11) NOT NULL,
 
 `title` varchar(32) NOT NULL,
 `time` int(32) NOT NULL,
 `mandatory` int(1),
 
 `userid` varchar(32) NOT NULL,
 
 `deleted` int(1),
 `created` int(32) NOT NULL,
 
 PRIMARY KEY (`id`),
 FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)
) 
```

```
CREATE TABLE `forms` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `userid` varchar(32) NOT NULL,
 `department_id` int(11) NOT NULL,
 `title` varchar(128) NOT NULL,
 `description` varchar(500) NOT NULL,
 `viewability` text NOT NULL,
 `viewability_responses` text NOT NULL,
 `published` int(1),
 `deleted` int(1),
 `created` int(32) NOT NULL,
 `modified` int(32),
 
 PRIMARY KEY (`id`),
 FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)
) 
```

```
CREATE TABLE `forms_elements` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `form_id` int(11) NOT NULL,
 `type` varchar(32) NOT NULL,
 `title` varchar(500) NOT NULL,
 `data` TEXT,
 `sorting` int(16) NOT NULL,
 `created` int(32) NOT NULL,
 
 PRIMARY KEY (`id`),
 FOREIGN KEY (`form_id`) REFERENCES `forms`(`id`)
) 
```

```
CREATE TABLE `forms_response` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `form_id` int(11) NOT NULL,
 `userid` varchar(32) NOT NULL,
 `archived` int(1),
 `created` int(32) NOT NULL,
 
 PRIMARY KEY (`id`),
 FOREIGN KEY (`form_id`) REFERENCES `forms`(`id`)
) 
```

```
CREATE TABLE `forms_response_answers` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `response_id` int(11) NOT NULL,
 `type` varchar(32) NOT NULL,
 `question` varchar(500) NOT NULL,
 `answer` TEXT NOT NULL,
 
 PRIMARY KEY (`id`),
 FOREIGN KEY (`response_id`) REFERENCES `forms_response`(`id`)
) 
```

```
CREATE TABLE `departments_announcements` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `department_id` int(32) NOT NULL,
 `title` varchar(64) NOT NULL,
 `desc` varchar(1000) NOT NULL,

 `userid` varchar(32) NOT NULL,

 `deleted` int(1),
 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`),
 FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`)
) 
```

```
CREATE TABLE `errors` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `error` varchar(1000) NOT NULL,
 `file` varchar(1000) NOT NULL,
 `line` int(32) NOT NULL,
 `userid` varchar(32),

 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`)
) 
```

```
CREATE TABLE `audit_logs` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `userid` varchar(32),
 `log` text NOT NULL,

 `created` int(32) NOT NULL,
 PRIMARY KEY (`id`)
) 
```