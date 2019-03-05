<?php

use yii\db\Migration;

/**
 * Class m190305_145632_init_db_rbac_tables
 */
class m190305_145632_init_db_rbac_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('roles', [
            'id' => $this->primaryKey(11)->unsigned(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->string(255),
            'parent_id' => $this->integer(11)->unsigned()
        ]);

        $this->createTable('permissions', [
            'id' => $this->primaryKey(11)->unsigned(),
            'name' => $this->string(255)->notNull(),
            'description' => $this->string(255),
            'parent_id' => $this->integer(11)->unsigned()
        ]);

        $this->createTable('roles_permissions', [
            'id' => $this->primaryKey(11)->unsigned(),
            'role_id' => $this->integer(11)->unsigned()->notNull(),
            'permission_id' => $this->integer(11)->unsigned()->notNull(),
        ]);

        $this->createTable('roles_users', [
            'id' => $this->primaryKey(11)->unsigned(),
            'role_id' => $this->integer(11)->unsigned()->notNull(),
            'user_id' => $this->bigPrimaryKey(20)->unsigned()->notNull(),
        ]);

        $this->addForeignKey('fk_roles_permissions_role', 'roles_permissions', 'role_id', 'roles', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_roles_permissions_permission', 'roles_permissions', 'permission_id', 'permissions', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_roles_role', 'roles', 'parent_id', 'roles', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_permissions_permission', 'permissions', 'parent_id', 'permissions', 'id', 'cascade', 'cascade');

        $this->addForeignKey('fk_roles_users_role', 'roles_users', 'role_id', 'roles', 'id', 'cascade', 'cascade');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('roles');
        $this->dropTable('permissions');
        $this->dropTable('roles_permissions');
        $this->dropTable('roles_users');
    }
}
