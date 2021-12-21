{{- define "helpers.container" }}
- name: {{ .name | quote }}
  image: "{{ .image }}:{{ .tag }}"
  resources:
    requests:
      memory: {{ (.resources).memoryRequest | default "128Mi" | quote }}
      cpu: {{ (.resources).cpuRequest | default "100m" | quote }}
    limits:
      memory: {{ (.resources).memoryLimit | default "256Mi" | quote }}
      cpu: {{ (.resources).cpuLimit | default "150m" | quote }}
{{ if .port }}
  ports:
    - name: http
      containerPort: {{ .port }}
      protocol: TCP
{{ end }}
{{ if .healthUrl }}
  livenessProbe:
    httpGet:
      path: {{ .healthUrl }}
      port: {{ .port | default "80" }}
    periodSeconds: 3
    failureThreshold: 10
{{ end }}
  envFrom:
    - configMapRef:
        name: common-config
        optional: false
    - secretRef:
        name: common-secret
        optional: false
{{ if .envFromConfig }}
{{- range .envFromConfig }}
    - configMapRef:
        name: {{ . | quote }}
        optional: true
{{- end }}
{{ end }}
{{ if .envFromSecret }}
{{- range .envFromSecret }}
    - secretRef:
        name: {{ . | quote }}
        optional: true
{{- end }}
{{ end }}
{{- end }}
