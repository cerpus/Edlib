{{- define "helpers.podDisruptionBudget" }}
apiVersion: policy/v1
kind: PodDisruptionBudget
metadata:
  name: {{ .name }}
spec:
  maxUnavailable: 50%
  selector:
    matchLabels:
      app: {{ .name }}
{{- end }}
