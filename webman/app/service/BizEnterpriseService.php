<?php

namespace app\service;

use app\model\BizEnterprise;
use app\model\BizPlan;

class BizEnterpriseService
{
    public function selectEnterpriseList($params = [])
    {
        $query = BizEnterprise::query();

        if (!empty($params['enterprise_name'])) {
            $query->where('enterprise_name', 'like', '%' . $params['enterprise_name'] . '%');
        }
        if (!empty($params['boss_name'])) {
            $query->where('boss_name', 'like', '%' . $params['boss_name'] . '%');
        }
        if (!empty($params['phone'])) {
            $query->where('phone', 'like', '%' . $params['phone'] . '%');
        }
        if (!empty($params['enterprise_type'])) {
            $query->where('enterprise_type', $params['enterprise_type']);
        }
        if (!empty($params['enterprise_level'])) {
            $query->where('enterprise_level', $params['enterprise_level']);
        }
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        $pageNum = intval($params['page_num'] ?? 1);
        $pageSize = intval($params['page_size'] ?? 10);
        $result = $query->orderBy('enterprise_id', 'desc')->paginate($pageSize, ['*'], 'page', $pageNum);

        $planCounts = BizPlan::selectRaw('enterprise_id, count(*) as plan_count')
            ->whereIn('enterprise_id', $result->pluck('enterprise_id')->toArray())
            ->groupBy('enterprise_id')
            ->pluck('plan_count', 'enterprise_id')
            ->toArray();

        foreach ($result as $enterprise) {
            $enterprise->plan_count = $planCounts[$enterprise->enterprise_id] ?? 0;
        }

        return $result;
    }

    public function selectEnterpriseById($enterpriseId)
    {
        return BizEnterprise::find($enterpriseId);
    }

    public function insertEnterprise($data)
    {
        $data['create_time'] = date('Y-m-d H:i:s');
        if (empty($data['pinyin']) && !empty($data['enterprise_name'])) {
            $data['pinyin'] = $this->getPinyin($data['enterprise_name']);
        }
        return BizEnterprise::create($data);
    }

    public function updateEnterprise($data)
    {
        $data['update_time'] = date('Y-m-d H:i:s');
        if (!empty($data['enterprise_name']) && empty($data['pinyin'])) {
            $data['pinyin'] = $this->getPinyin($data['enterprise_name']);
        }
        return BizEnterprise::where('enterprise_id', $data['enterprise_id'])->update($data);
    }

    public function selectEnterpriseForSearch($keyword)
    {
        $query = BizEnterprise::query();
        
        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('enterprise_name', 'like', '%' . $keyword . '%')
                  ->orWhere('pinyin', 'like', '%' . $keyword . '%');
            });
        }
        
        return $query->where('status', '0')
                    ->orderBy('enterprise_name', 'asc')
                    ->limit(50)
                    ->get();
    }

    public function getPinyin($text)
    {
        $pinyin = '';
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($chars as $char) {
            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $char)) {
                $pinyin .= $this->charToPinyin($char);
            } else {
                $pinyin .= strtoupper($char);
            }
        }
        
        return $pinyin;
    }

    private function charToPinyin($char)
    {
        $pinyinMap = [
            'a' => ['阿', '啊', '安'],
            'b' => ['不', '把', '被', '本', '比', '变', '别', '并', '波', '部'],
            'c' => ['才', '采', '参', '操', '策', '曾', '差', '产', '常', '长', '朝', '车', '成', '城', '程', '承', '吃', '冲', '出', '处', '传', '创', '春', '词', '从', '促', '存', '措'],
            'd' => ['大', '打', '代', '当', '导', '到', '得', '的', '等', '地', '第', '典', '调', '定', '东', '动', '都', '度', '段', '断', '队', '对', '顿', '多'],
            'e' => ['而', '恩'],
            'f' => ['发', '法', '反', '范', '方', '防', '放', '非', '费', '分', '丰', '风', '封', '夫', '服', '复', '付', '负', '富'],
            'g' => ['改', '概', '干', '刚', '高', '搞', '告', '格', '个', '给', '更', '工', '公', '功', '攻', '供', '构', '购', '估', '固', '关', '管', '惯', '广', '规', '国', '过'],
            'h' => ['还', '海', '含', '行', '好', '合', '和', '很', '红', '后', '互', '化', '环', '换', '回', '会', '活', '获', '或', '基', '机', '及', '即', '级', '极', '急', '集', '几', '计', '记', '际', '济', '继', '技', '加', '家', '价', '坚', '间', '检', '简', '建', '健', '渐', '将', '讲', '交', '教', '阶', '接', '结', '解', '界', '借', '今', '金', '进', '近', '经', '精', '竞', '就', '局', '具', '据', '决', '均'],
            'k' => ['开', '看', '科', '可', '克', '客', '课', '控', '口', '快', '宽', '况', '困', '扩'],
            'l' => ['来', '劳', '乐', '类', '离', '理', '力', '立', '利', '例', '连', '联', '量', '料', '列', '领', '另', '流', '六', '录', '路', '率', '论', '落'],
            'm' => ['吗', '买', '满', '慢', '忙', '毛', '矛', '贸', '么', '每', '美', '门', '们', '面', '苗', '描', '民', '明', '命', '模', '某', '目', '幕'],
            'n' => ['拿', '那', '难', '内', '能', '年', '念', '您', '宁'],
            'o' => ['哦'],
            'p' => ['排', '判', '配', '跑', '培', '批', '片', '偏', '品', '平', '评', '破', '普'],
            'q' => ['期', '其', '奇', '齐', '起', '气', '器', '千', '前', '钱', '强', '切', '亲', '轻', '清', '情', '求', '区', '取', '去', '趣', '权', '全', '确', '群'],
            'r' => ['然', '让', '人', '认', '任', '日', '容', '入', '软'],
            's' => ['三', '散', '色', '山', '善', '上', '尚', '稍', '社', '设', '深', '甚', '生', '省', '盛', '剩', '失', '实', '识', '时', '世', '市', '示', '式', '事', '是', '适', '收', '手', '首', '受', '输', '数', '双', '税', '顺', '说', '思', '死', '四', '似', '松', '速', '随', '损', '缩', '所', '索'],
            't' => ['他', '它', '她', '台', '太', '谈', '特', '提', '题', '体', '天', '条', '铁', '通', '同', '统', '投', '透', '突', '图', '团', '推'],
            'w' => ['外', '完', '玩', '往', '危', '为', '位', '文', '稳', '问', '我', '无', '五', '物'],
            'x' => ['西', '吸', '希', '息', '习', '系', '细', '下', '先', '现', '限', '线', '相', '向', '项', '消', '小', '效', '些', '写', '新', '信', '行', '形', '性', '需', '许', '序', '选', '学', '雪', '寻', '训', '迅'],
            'y' => ['压', '研', '严', '言', '颜', '演', '验', '阳', '样', '要', '也', '业', '一', '依', '移', '以', '易', '意', '义', '因', '应', '迎', '赢', '盈', '影', '硬', '用', '由', '尤', '有', '又', '右', '于', '余', '与', '予', '元', '原', '源', '远', '愿', '月', '越', '云', '允', '运'],
            'z' => ['杂', '在', '咱', '暂', '造', '则', '怎', '增', '展', '占', '站', '张', '找', '照', '这', '真', '整', '正', '证', '政', '之', '支', '知', '直', '值', '职', '指', '制', '质', '治', '中', '种', '重', '周', '洲', '主', '注', '驻', '专', '转', '装', '准', '资', '总', '组', '最', '作', '坐', '做']
        ];
        
        foreach ($pinyinMap as $py => $chars) {
            if (in_array($char, $chars)) {
                return strtoupper($py);
            }
        }
        
        return '';
    }

    public function deleteEnterpriseByIds($enterpriseIds)
    {
        return BizEnterprise::whereIn('enterprise_id', $enterpriseIds)->delete();
    }

    public function updateEnterpriseStatus($enterpriseId, $status)
    {
        return BizEnterprise::where('enterprise_id', $enterpriseId)->update([
            'status' => $status,
            'update_time' => date('Y-m-d H:i:s')
        ]);
    }
}
