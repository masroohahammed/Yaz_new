<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\PmFormFields;
use Config\PmModules;

class PmCrudService
{
  public function __construct(private BaseConnection $db)
  {
  }

  public function row(string $slug, int $id, ?int $companyId = null): ?array
  {
    $module = PmModules::get($slug);
    if (! $module || ! $this->db->tableExists($module['table'])) {
      return null;
    }

    $builder = $this->db->table($module['table'])->where('id', $id);
    if ($this->db->fieldExists('deleted_at', $module['table'])) {
      $builder->where('deleted_at', null);
    }
    if ($companyId && $this->db->fieldExists('company_id', $module['table'])) {
      $builder->where('company_id', $companyId);
    }

    return $builder->get()->getRowArray() ?: null;
  }

  /**
   * @return array{rows: list<array<string, mixed>>, total: int}
   */
  public function list(string $slug, ?int $companyId, string $search = '', int $limit = 50, int $offset = 0): array
  {
    $module = PmModules::get($slug);
    if (! $module || ! $this->db->tableExists($module['table'])) {
      return ['rows' => [], 'total' => 0];
    }

    $table = $module['table'];
    $q     = $this->db->table($table);
    if ($this->db->fieldExists('deleted_at', $table)) {
      $q->where('deleted_at', null);
    }
    if ($companyId && $this->db->fieldExists('company_id', $table)) {
      $q->where('company_id', $companyId);
    }
    if ($search !== '' && ! empty($module['search'])) {
      $q->groupStart();
      foreach ($module['search'] as $i => $col) {
        if (! $this->db->fieldExists($col, $table)) {
          continue;
        }
        $i === 0 ? $q->like($col, $search) : $q->orLike($col, $search);
      }
      $q->groupEnd();
    }

    $total = $q->countAllResults(false);
    $order = $module['columns'][0]['key'] ?? 'id';
    if (! $this->db->fieldExists($order, $table)) {
      $order = 'id';
    }
    $rows = $q->orderBy($order, 'ASC')->limit($limit, $offset)->get()->getResultArray();

    return ['rows' => $rows, 'total' => $total];
  }

  /** @return array<string, string> */
  public function fkOptions(array $field, ?int $companyId, ?callable $scopeCompany = null): array
  {
    if (($field['type'] ?? '') === 'fk_user') {
      $q = $this->db->table('users u')
        ->select('u.id, u.name')
        ->join('roles r', 'r.id = u.role_id', 'left')
        ->where('u.status', 'active')
        ->orderBy('u.name');
      if ($companyId) {
        $q->where('u.company_id', $companyId);
      }
      if ($this->db->fieldExists('deleted_at', 'users')) {
        $q->where('u.deleted_at', null);
      }

      return $this->pairs($q->get()->getResultArray(), 'id', 'name');
    }

    if (($field['type'] ?? '') !== 'fk') {
      return [];
    }

    $table   = $field['table'];
    $display = $field['display'];
    if (! $this->db->tableExists($table)) {
      return [];
    }

    $q = $this->db->table($table)->select("id, {$display} AS label")->orderBy($display);
    if ($this->db->fieldExists('deleted_at', $table)) {
      $q->where('deleted_at', null);
    }
    if ($companyId && $this->db->fieldExists('company_id', $table)) {
      $q->where('company_id', $companyId);
    }
    if ($table === 'facilities' && $scopeCompany) {
      $scopeCompany($q);
    }

    return $this->pairs($q->get()->getResultArray(), 'id', 'label');
  }

  /** @return array<string, mixed> */
  public function validateAndBuild(string $slug, array $post, array $user, bool $isEdit): array
  {
    $module = PmModules::get($slug);
    $table  = $module['table'];
    $errors = [];
    $data   = [];

    foreach (PmFormFields::sections($slug) as $section) {
      foreach ($section['fields'] as $field) {
        $name = $field['name'];
        if (! $this->db->fieldExists($name, $table)) {
          continue;
        }

        $raw = $post[$name] ?? null;
        if (($field['type'] ?? '') === 'checkbox') {
          $data[$name] = ! empty($raw) ? 1 : 0;
          continue;
        }

        if ($raw === '' || $raw === null) {
          if (! empty($field['required']) && ! $isEdit) {
            $errors[$name] = $field['label'] . ' is required.';
          }
          $data[$name] = null;
          continue;
        }

        $data[$name] = match ($field['type'] ?? 'text') {
          'number' => is_numeric($raw) ? $raw : null,
          'fk', 'fk_user' => (int) $raw > 0 ? (int) $raw : null,
          default  => trim((string) $raw),
        };

        if (! empty($field['required']) && ($data[$name] === null || $data[$name] === '')) {
          $errors[$name] = $field['label'] . ' is required.';
        }
      }
    }

    if (! $isEdit && ! empty($user['company_id']) && $this->db->fieldExists('company_id', $table)) {
      $data['company_id'] = (int) $user['company_id'];
    }

    if (! $isEdit && $this->db->fieldExists('created_by', $table)) {
      $data['created_by'] = (int) $user['id'];
    }

    if ($this->db->fieldExists('created_at', $table) && ! $isEdit) {
      $data['created_at'] = date('Y-m-d H:i:s');
    }
    if ($this->db->fieldExists('updated_at', $table)) {
      $data['updated_at'] = date('Y-m-d H:i:s');
    }

    return ['data' => $data, 'errors' => $errors];
  }

  public function autoNumber(string $slug, callable $generateNumber): ?string
  {
    $module = PmModules::get($slug);
    if (empty($module['number'])) {
      return null;
    }

    return $generateNumber(
      $module['number']['prefix'],
      $module['table'],
      $module['number']['field']
    );
  }

  /** @param list<array<string, mixed>> $rows */
  private function pairs(array $rows, string $idKey, string $labelKey): array
  {
    $out = [];
    foreach ($rows as $row) {
      $out[(string) $row[$idKey]] = (string) ($row[$labelKey] ?? $row['label'] ?? '');
    }

    return $out;
  }
}
