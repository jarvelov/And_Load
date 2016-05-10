<?php $this->layout('template', array('active_tab' => 'tab_editor')); ?>

<div class="row">
    <div class="col-xs-12">
        <?php
            function label($content = false, $key, $setting) {
                $html = '<label for="' . $key . '" class="control-label" title="' . $setting['description'] . '">';

                if($content !== false) {
                    $html .= $content;
                }

                $html .= ucfirst( $setting['name'] );
                $html .= '</label>';

                return ;
            }

            function formGroup($args, $atts) {
                $atts = array_replace_recursive( array(
                    'class' => 'form-control',
                ), $atts);

                $dom = new DOMDocument('1.0');

                $classes = array(
                    'form-group'
                );

                $formGroup = $dom->createElement('div');

                $dom->appendChild($formGroup);

                $label = $dom->createElement('label');
                $label->setAttribute('class', 'control-label');
                $label->setAttribute('for', $atts['id']);
                $label->setAttribute('title', $args['title']);
                $label->setAttribute('data-toggle', 'tooltip');

                $labelName = $dom->createElement('span', $args['label']);
                $formGroup->appendChild($label);

                switch ($atts['type']) {
                    case 'text':
                    case 'number':
                        $element = $dom->createElement('input');
                        $element->setAttribute('type', $atts['type']);
                        $formGroup->appendChild($element);
                        $label->appendChild($labelName);
                        break;
                    case 'checkbox':
                        $checkbox_div = $dom->createElement('div');
                        $checkbox_div->setAttribute('class', 'checkbox');
                        $checkbox_div->appendChild($label);
                        $formGroup->appendChild($checkbox_div);

                        $element = $dom->createElement('input');
                        $element->setAttribute('type', $atts['type']);

                        $label->appendChild($element);
                        $label->appendChild($labelName);
                        break;
                    
                    case 'select':
                        $label->appendChild($labelName);
                        $element = $dom->createElement('select');
                        $formGroup->appendChild($element);

                        foreach ($args['options'] as $key => $value) {
                            $option = $dom->createElement('option', $value);
                            $option->setAttribute('value', $key );
                            $element->appendChild($option);
                        }

                        break;
                }

                $formGroup->setAttribute('class', implode($classes, ' '));

                foreach ($atts as $key => $value) {
                    $element->setAttribute($key, $value);
                }

                return $dom->saveHTML();
            }

            foreach ($settings as $key => $setting) {
                $args = array(
                    'label' => $setting['name'],
                    'title' => $setting['description']
                );

                $atts = array(
                    'id' => $key,
                    'type' => $setting['type'],
                    'value' => $setting['value'],
                    'name' => 'and_load_default_options[' . $key . ']'
                );

                switch($setting['type']) {
                    case 'checkbox':
                        $atts['checked'] = ( $setting['value'] === $setting['default'] ) ? 'checked' : '';
                        break;
                    case 'select':
                        $args['options'] = $setting['values'];
                        break;
                }

                $html = formGroup($args, $atts);
                echo $html;
            }
        ?>
    </div>
</div>