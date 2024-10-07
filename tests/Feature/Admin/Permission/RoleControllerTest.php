<?php

declare(strict_types=1);
/**
 * This file is part of MineAdmin.
 *
 * @link     https://www.mineadmin.com
 * @document https://doc.mineadmin.com
 * @contact  root@imoi.cn
 * @license  https://github.com/mineadmin/MineAdmin/blob/master/LICENSE
 */

namespace HyperfTests\Feature\Admin\Permission;

use App\Http\Common\ResultCode;
use App\Model\Permission\Menu;
use App\Model\Permission\Role;
use Hyperf\Database\Model\ModelNotFoundException;
use Hyperf\Stringable\Str;
use HyperfTests\Feature\Admin\ControllerCase;

/**
 * @internal
 * @coversNothing
 */
final class RoleControllerTest extends ControllerCase
{
    public function testPageList(): void
    {
        $token = $this->token;
        $result = $this->get('/admin/role/list');
        self::assertSame($result['code'], ResultCode::UNAUTHORIZED->value);
        $result = $this->get('/admin/role/list', ['token' => $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        self::assertFalse($enforce->hasPermissionForUser($this->user->username, 'role:list'));
        self::assertTrue($enforce->addPermissionForUser($this->user->username, 'role:list'));
        self::assertTrue($enforce->hasPermissionForUser($this->user->username, 'role:list'));
        $result = $this->get('/admin/role/list', ['token' => $token]);
        self::assertSame($result['code'], ResultCode::SUCCESS->value);
        self::assertTrue($enforce->deletePermissionForUser($this->user->username, 'role:list'));
        $result = $this->get('/admin/role/list', ['token' => $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
    }

    public function testCreate(): void
    {
        $token = $this->token;
        $attribute = [
            'name',
            'code',
            'sort',
            'status',
            'remark',
        ];
        $result = $this->post('/admin/role');
        self::assertSame($result['code'], ResultCode::UNPROCESSABLE_ENTITY->value);
        $result = $this->post('/admin/role', [], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::UNPROCESSABLE_ENTITY->value);
        $fill = [
            'name' => Str::random(10),
            'code' => Str::random(10),
            'sort' => rand(1, 100),
            'status' => rand(1, 2),
            'remark' => Str::random(),
        ];
        $result = $this->post('/admin/role', $fill, ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        self::assertFalse($enforce->hasPermissionForUser($this->user->username, 'role:create'));
        self::assertTrue($enforce->addPermissionForUser($this->user->username, 'role:create'));
        self::assertTrue($enforce->hasPermissionForUser($this->user->username, 'role:create'));
        $result = $this->post('/admin/role', $fill, ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::SUCCESS->value);
        self::assertTrue($enforce->deletePermissionForUser($this->user->username, 'role:create'));
        $result = $this->post('/admin/role', $fill, ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
        $entity = Role::query()->where('code', $fill['code'])->first();
        self::assertNotNull($entity);
        self::assertSame($entity->name, $fill['name']);
        self::assertSame($entity->sort, $fill['sort']);
        self::assertSame($entity->status, $fill['status']);
        self::assertSame($entity->remark, $fill['remark']);
        $entity->forceDelete();
    }

    public function testSave(): void
    {
        $token = $this->token;
        $entity = Role::create([
            'name' => Str::random(10),
            'code' => Str::random(10),
            'sort' => rand(1, 100),
            'status' => rand(1, 2),
            'remark' => Str::random(),
        ]);
        $result = $this->put('/admin/role/' . $entity->id);
        self::assertSame($result['code'], ResultCode::UNPROCESSABLE_ENTITY->value);
        $result = $this->put('/admin/role/' . $entity->id, [], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::UNPROCESSABLE_ENTITY->value);
        $fill = [
            'name' => Str::random(10),
            'code' => Str::random(10),
            'sort' => rand(1, 100),
            'status' => rand(1, 2),
            'remark' => Str::random(),
        ];
        $result = $this->put('/admin/role/' . $entity->id, $fill, ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        self::assertFalse($enforce->hasPermissionForUser($this->user->username, 'role:save'));
        self::assertTrue($enforce->addPermissionForUser($this->user->username, 'role:save'));
        self::assertTrue($enforce->hasPermissionForUser($this->user->username, 'role:save'));
        $result = $this->put('/admin/role/' . $entity->id, $fill, ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::SUCCESS->value);
        self::assertTrue($enforce->deletePermissionForUser($this->user->username, 'role:save'));
        $result = $this->put('/admin/role/' . $entity->id, $fill, ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
        $entity->refresh();
        self::assertSame($entity->name, $fill['name']);
        self::assertSame($entity->sort, $fill['sort']);
        self::assertSame($entity->status, $fill['status']);
        self::assertSame($entity->remark, $fill['remark']);
        $entity->forceDelete();
    }

    public function testDelete(): void
    {
        $token = $this->token;
        $entity = Role::create([
            'name' => Str::random(10),
            'code' => Str::random(10),
            'sort' => rand(1, 100),
            'status' => rand(1, 2),
            'remark' => Str::random(),
        ]);
        $result = $this->delete('/admin/role');
        self::assertSame($result['code'], ResultCode::UNAUTHORIZED->value);
        $result = $this->delete('/admin/role', [], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
        $enforce = $this->getEnforce();
        self::assertFalse($enforce->hasPermissionForUser($this->user->username, 'role:delete'));
        self::assertTrue($enforce->addPermissionForUser($this->user->username, 'role:delete'));
        self::assertTrue($enforce->hasPermissionForUser($this->user->username, 'role:delete'));
        $result = $this->delete('/admin/role', [$entity->id], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::SUCCESS->value);
        self::assertTrue($enforce->deletePermissionForUser($this->user->username, 'role:delete'));
        $result = $this->delete('/admin/role', [$entity->id], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
        $this->expectException(ModelNotFoundException::class);
        $entity->refresh();
    }

    public function testBatchGrantPermissionsForRole(): void
    {
        $menus = [
            Menu::create([
                'parent_id' => 0,
                'name' => Str::random(10),
                'code' => Str::random(10),
                'icon' => Str::random(10),
                'route' => Str::random(10),
                'component' => Str::random(10),
                'redirect' => Str::random(10),
                'is_hidden' => rand(1, 2),
                'type' => Str::random(1),
                'status' => rand(1, 2),
                'sort' => rand(1, 100),
                'remark' => Str::random(10),
            ]),
            Menu::create([
                'parent_id' => 0,
                'name' => Str::random(10),
                'code' => Str::random(10),
                'icon' => Str::random(10),
                'route' => Str::random(10),
                'component' => Str::random(10),
                'redirect' => Str::random(10),
                'is_hidden' => rand(1, 2),
                'type' => Str::random(1),
                'status' => rand(1, 2),
                'sort' => rand(1, 100),
                'remark' => Str::random(10),
            ]),
            Menu::create([
                'parent_id' => 0,
                'name' => Str::random(10),
                'code' => Str::random(10),
                'icon' => Str::random(10),
                'route' => Str::random(10),
                'component' => Str::random(10),
                'redirect' => Str::random(10),
                'is_hidden' => rand(1, 2),
                'type' => Str::random(1),
                'status' => rand(1, 2),
                'sort' => rand(1, 100),
                'remark' => Str::random(10),
            ]),
        ];
        $menuIds = array_column($menus, 'id');
        $codes = array_column($menus, 'code');
        $role = Role::create([
            'name' => Str::random(10),
            'code' => Str::random(10),
            'sort' => rand(1, 100),
            'status' => rand(1, 2),
            'remark' => Str::random(),
        ]);
        $token = $this->token;
        $enforce = $this->getEnforce();
        foreach ($codes as $code) {
            self::assertFalse($enforce->hasPermissionForUser($role->code, $code));
            self::assertTrue($enforce->addPermissionForUser($role->code, $code));
            self::assertTrue($enforce->hasPermissionForUser($role->code, $code));
        }
        $uri = '/admin/role/setRolePermission/' . $role->id;
        $result = $this->put($uri);
        self::assertSame($result['code'], ResultCode::UNPROCESSABLE_ENTITY->value);
        $result = $this->put($uri, [], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::UNPROCESSABLE_ENTITY->value);
        $result = $this->put($uri, ['permission_ids' => $menuIds], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::FORBIDDEN->value);
        $userRole = Role::create([
            'name' => Str::random(10),
            'code' => Str::random(10),
            'sort' => rand(1, 100),
            'status' => rand(1, 2),
            'remark' => Str::random(),
        ]);
        self::assertFalse($enforce->hasRoleForUser($this->user->username, $userRole->code));
        self::assertTrue($enforce->addRoleForUser($this->user->username, $userRole->code));
        self::assertTrue($enforce->hasRoleForUser($this->user->username, $userRole->code));
        self::assertTrue($enforce->addPermissionForUser($userRole->code, 'role:setPermission'));
        self::assertTrue($enforce->hasPermissionForUser($userRole->code, 'role:setPermission'));
        $result = $this->put($uri, ['permission_ids' => $menuIds], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::SUCCESS->value);
        self::assertTrue($enforce->addRoleForUser($this->user->username, $role->code));
        $this->asserRolePermission($role->code, $codes);

        // Delete role permission
        foreach ($codes as $code) {
            self::assertTrue($enforce->hasPermissionForUser($role->code, $code));
            self::assertTrue($enforce->deletePermissionForUser($role->code, $code));
            self::assertFalse($enforce->hasPermissionForUser($role->code, $code));
        }
        $this->asserRolePermission($role->code, $codes, false);

        foreach ($codes as $code) {
            self::assertFalse($enforce->hasPermissionForUser($role->code, $code));
            self::assertTrue($enforce->addPermissionForUser($role->code, $code));
            self::assertTrue($enforce->hasPermissionForUser($role->code, $code));
        }
        $this->asserRolePermission($role->code, $codes);

        $result = $this->put($uri, ['permission_ids' => $menuIds], ['Authorization' => 'Bearer ' . $token]);
        self::assertSame($result['code'], ResultCode::SUCCESS->value);
        $this->asserRolePermission($role->code, $codes);
        $enforce->deleteRole($role->code);
        $this->asserRolePermission($role->code, $codes, false);

        $role->forceDelete();
        Menu::query()->whereIn('id', $menuIds)->forceDelete();
    }

    public function asserRolePermission(string $roleCode, array $codes, bool $in = true): void
    {
        $enforce = $this->getEnforce();
        $allPermission = $enforce->getImplicitPermissionsForUser($roleCode);
        $all = [];
        array_walk_recursive($allPermission, static function ($value) use (&$all) {
            $all[] = $value;
        });

        foreach ($codes as $code) {
            $in ? self::assertTrue(\in_array($code, $all, true))
                : self::assertFalse(\in_array($code, $all, true));
        }
    }
}
