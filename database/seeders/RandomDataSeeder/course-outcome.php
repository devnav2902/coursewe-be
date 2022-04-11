<?php
function courseOutcome($id)
{
  return [
    ['description' => 'Bạn sẽ nắm được các kiến thức cơ bản', 'course_id' => $id, 'order' => 1],
    ['description' => 'Cơ sở để nâng cao trình độ chuyên môn', 'course_id' => $id, 'order' => 2]
  ];
}
