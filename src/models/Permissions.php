<?php

namespace vladayson\AccessRules\models;

use Yii;
use lhs\Yii2SaveRelationsBehavior\SaveRelationsBehavior;

/**
 * This is the model class for table "permissions".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $parent_id
 *
 * @property Permissions $parent
 * @property Permissions[] $permissions
 * @property Roles[] $roles
 * @property RolesPermissions[] $rolesPermissions
 */
class Permissions extends BaseModel
{

    public function behaviors()
    {
        return [
            'saveRelations' => [
                'class'     => SaveRelationsBehavior::class,
                'relations' => [
                    'permissions',
                    'roles'
                ],
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'permissions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id'], 'integer'],
            [['name', 'description'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Permissions::class, 'targetAttribute' => ['parent_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'parent_id' => 'Parent ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Permissions::class, ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPermissions()
    {
        return $this->hasMany(Permissions::class, ['parent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRolesPermissions()
    {
        return $this->hasMany(RolesPermissions::class, ['permission_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoles()
    {
        return $this->hasMany(Roles::class, ['role_id' => 'id'])->via('rolesPermissions');
    }

    /**
     * {@inheritdoc}
     * @return PermissionsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new PermissionsQuery(get_called_class());
    }

    /**
     * @param $userId
     * @param $permissionName
     *
     * @return array|Permissions[]
     */
    public static function getUserPermissions($userId, $permissionName = null)
    {
        $rolesPermissionsTable = RolesPermissions::tableName();
        $rolesUsersTable = RolesUsers::tableName();
        $permissionsTable = Permissions::tableName();
        $rolesTable = Roles::tableName();

        $data = Permissions::find()
            ->innerJoin(
                $rolesPermissionsTable,
                "({$rolesPermissionsTable}.permission_id = {$permissionsTable}.id OR {$rolesPermissionsTable}.permission_id = {$permissionsTable}.parent_id)"
            )
            ->innerJoin(
                $rolesTable,
                "({$rolesPermissionsTable}.role_id = {$rolesTable}.id OR {$rolesPermissionsTable}.role_id = {$rolesTable}.parent_id)"
            )
            ->innerJoin(
                $rolesUsersTable,
                "({$rolesUsersTable}.role_id = {$rolesTable}.id OR {$rolesPermissionsTable}.role_id = {$rolesTable}.parent_id)"
            )
            ->andWhere(["{$rolesUsersTable}.user_id" => $userId]);
        if ($permissionName) {
            $data = $data->andWhere(["{$permissionsTable}.name" => $permissionName]);
        }

        return $data->all();
    }
}
